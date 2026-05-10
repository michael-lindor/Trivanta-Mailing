<?php

namespace App\Services;

use Illuminate\Support\Str;

class SpamScoringService
{
    protected const INTERNAL_SCORE_CAP = 100;
    protected const CATEGORY_WEIGHTS = [
        'domain_auth' => 30,
        'sender_identity' => 20,
        'subject' => 15,
        'content_quality' => 15,
        'link_quality' => 10,
        'image_text' => 10,
    ];

    /**
     * Calculate spam score for email content.
     * 
     * @param string $subject Email subject
     * @param string $htmlContent Email HTML content
     * @param string $textContent Email plain text content
     * @param array $options Additional options (from email, reply-to, etc.)
     * @return array Score details with recommendations
     */
    public function calculateSpamScore(string $subject, string $htmlContent, string $textContent, array $options = []): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];
        $incompleteAnalysis = false;
        $incompleteReasons = [];

        $allText = $subject . ' ' . strip_tags($htmlContent) . ' ' . $textContent;

        // 1. Domain Authentication
        $domainAuthScore = $this->scoreDomainAuthentication($options);
        $domainAuthScore = $this->applyCategoryWeight('domain_auth', $domainAuthScore);
        $score += $domainAuthScore['score'];
        $issues = array_merge($issues, $domainAuthScore['issues']);
        $warnings = array_merge($warnings, $domainAuthScore['warnings']);
        $recommendations = array_merge($recommendations, $domainAuthScore['recommendations']);
        if (!empty($domainAuthScore['incomplete'])) {
            $incompleteAnalysis = true;
            $incompleteReasons = array_merge($incompleteReasons, $domainAuthScore['incomplete_reasons'] ?? []);
        }

        // 2. Sender Identity
        $senderScore = $this->scoreSenderInfo($options);
        $senderScore = $this->applyCategoryWeight('sender_identity', $senderScore);
        $score += $senderScore['score'];
        $issues = array_merge($issues, $senderScore['issues']);
        $warnings = array_merge($warnings, $senderScore['warnings']);
        $recommendations = array_merge($recommendations, $senderScore['recommendations']);
        if (!empty($senderScore['incomplete'])) {
            $incompleteAnalysis = true;
            $incompleteReasons = array_merge($incompleteReasons, $senderScore['incomplete_reasons'] ?? []);
        }

        // 3. Link Quality
        $linkScore = $this->scoreLinkQuality($htmlContent, $textContent);
        $linkScore = $this->applyCategoryWeight('link_quality', $linkScore);
        $score += $linkScore['score'];
        $issues = array_merge($issues, $linkScore['issues']);
        $warnings = array_merge($warnings, $linkScore['warnings']);
        $recommendations = array_merge($recommendations, $linkScore['recommendations']);

        // 4. Image-to-Text Ratio
        $imageTextScore = $this->scoreImageToTextRatio($htmlContent);
        $imageTextScore = $this->applyCategoryWeight('image_text', $imageTextScore);
        $score += $imageTextScore['score'];
        $issues = array_merge($issues, $imageTextScore['issues']);
        $warnings = array_merge($warnings, $imageTextScore['warnings']);
        $recommendations = array_merge($recommendations, $imageTextScore['recommendations']);

        // 5. Subject Line Analysis
        $subjectScore = $this->scoreSubject($subject);
        $subjectScore = $this->applyCategoryWeight('subject', $subjectScore);
        $score += $subjectScore['score'];
        $issues = array_merge($issues, $subjectScore['issues']);
        $warnings = array_merge($warnings, $subjectScore['warnings']);
        $recommendations = array_merge($recommendations, $subjectScore['recommendations']);

        // 6. Content Quality (body text + HTML structure + text patterns)
        $contentScore = $this->scoreContentQuality($htmlContent, $textContent, $allText);
        $contentScore = $this->applyCategoryWeight('content_quality', $contentScore);
        $score += $contentScore['score'];
        $issues = array_merge($issues, $contentScore['issues']);
        $warnings = array_merge($warnings, $contentScore['warnings']);
        $recommendations = array_merge($recommendations, $contentScore['recommendations']);
        if (!empty($contentScore['incomplete'])) {
            $incompleteAnalysis = true;
            $incompleteReasons = array_merge($incompleteReasons, $contentScore['incomplete_reasons'] ?? []);
        }

        $score = min(100, max(0, $score));
        $deliverabilityScore = $score;
        $assessment = $this->getAssessment($deliverabilityScore);
        $shouldBlock = $deliverabilityScore < $this->getBlockingThreshold() || $incompleteAnalysis;
        $scorePercent = $this->toPercent($deliverabilityScore);

        return [
            'score' => $deliverabilityScore,
            'penalty_score' => max(0, 100 - $deliverabilityScore),
            'deliverability_score' => $deliverabilityScore,
            'score_percent' => $scorePercent,
            'assessment' => $assessment,
            'risk_tone' => $this->getRiskTone($deliverabilityScore),
            'should_block' => $shouldBlock,
            'blocking_threshold' => $this->getBlockingThreshold(),
            'incomplete_analysis' => $incompleteAnalysis,
            'incomplete_reasons' => array_values(array_unique($incompleteReasons)),
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations,
            'remarks' => $this->buildRemarks($issues, $warnings, $recommendations),
            'breakdown' => [
                'domain_auth'     => $domainAuthScore,
                'sender_identity' => $senderScore,
                'link_quality'    => $linkScore,
                'image_text'      => $imageTextScore,
                'subject'         => $subjectScore,
                'content_quality' => $contentScore,
            ],
            'checks' => [
                $this->buildCheck('Domain Authentication', $domainAuthScore),
                $this->buildCheck('Sender Identity',       $senderScore),
                $this->buildCheck('Link Quality',          $linkScore),
                $this->buildCheck('Image-to-Text Ratio',   $imageTextScore),
                $this->buildCheck('Subject Line Analysis', $subjectScore),
                $this->buildCheck('Content Quality',       $contentScore),
            ],
        ];
    }

    /**
     * Score subject line for spam indicators.
     */
    protected function scoreSubject(string $subject): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $trimmedSubject = trim($subject);
        if ($trimmedSubject === '') {
            return [
                'score'           => 0,
                'issues'          => [],
                'warnings'        => [],
                'recommendations' => [],
                'pending'         => true,
                'pending_message' => 'Waiting for subject line input to analyze length and words.',
            ];
        }

        $subjectLower = strtolower($subject);

        // High-impact spam triggers
        $spammyWords = ['free', 'winner', 'congratulations', 'urgent', 'act now', 'limited time', 'special promotion', 'exclusive deal', 'risk free', 'guarantee', '100% free', 'no cost', 'click here', 'order now'];
        foreach ($spammyWords as $word) {
            if (strpos($subjectLower, $word) !== false) {
                $score += 10;
                $issues[] = "Subject contains spammy word: '{$word}'";
                break;
            }
        }

        // All caps subject
        if (strtoupper($subject) === $subject && strlen($subject) > 3) {
            $score += 5;
            $issues[] = 'Subject is in all caps';
            $recommendations[] = 'Use normal capitalization in subject';
        }

        // Excessive punctuation
        $exclamationCount = substr_count($subject, '!') + substr_count($subject, '?');
        if ($exclamationCount > 2) {
            $score += 5;
            $issues[] = 'Subject has excessive punctuation (' . $exclamationCount . ' punctuation marks)';
            $recommendations[] = 'Limit punctuation to 1-2 marks in subject';
        }   

        // Subject length issues
        if (strlen($subject) < 5) {
            $score += 5;
            $warnings[] = 'Subject is very short';
            $recommendations[] = 'Use a more descriptive subject (5-50 characters)';
        } elseif (strlen($subject) > 100) {
            $score += 5;
            $warnings[] = 'Subject is very long';
            $recommendations[] = 'Keep subject under 100 characters';
        }

        // Numbers and special characters
        if (preg_match('/\$\d+/', $subject)) {
            $score += 5;
            $issues[] = 'Subject contains dollar amounts';
        }

        if (preg_match('/\d+%/', $subject)) {
            $score += 5;
            $warnings[] = 'Subject contains percentages';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score email content for spam indicators.
     */
    protected function scoreContent(string $htmlContent, string $textContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $plainFromHtml = trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent)));
        $plainText = trim(preg_replace('/\s+/', ' ', $plainFromHtml . ' ' . $textContent));
        $textLower = strtolower($plainText);

        if ($plainText === '') {
            $score += 5;
            $issues[] = 'Email content is empty';
            $recommendations[] = 'Add meaningful email body text in the builder before sending.';
        } elseif (strlen($plainText) < 40) {
            $score += 3;
            $warnings[] = 'Email content is very short';
            $recommendations[] = 'Add more body copy so subscribers have clear context and intent.';
        }

        $placeholderPhrases = [
            'lorem ipsum',
            'your content here',
            'start writing',
            'drag and drop',
            'double click to edit',
        ];

        foreach ($placeholderPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 2;
                $warnings[] = 'Email content appears to include placeholder text';
                $recommendations[] = 'Replace placeholder text with final campaign copy.';
                break;
            }
        }

        // Check for spammy phrases
        $spammyPhrases = [
            'click here', 'order now', 'buy now', 'limited time', 'act now', 'don\'t delete',
            'free money', 'cash bonus', 'risk free', 'no obligation', 'special promotion',
            'exclusive offer', 'once in a lifetime', 'urgent', 'immediate action required'
        ];

        foreach ($spammyPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 2;
                $issues[] = "Content contains spammy phrase: '{$phrase}'";
            }
        }

        // Text-to-image ratio (if HTML has images)
        $imageCount = substr_count($htmlContent, '<img');
        $textLength = strlen($plainFromHtml);
        
        if ($imageCount > 0 && $textLength < 200) {
            $score += 3;
            $issues[] = 'Low text-to-image ratio';
            $recommendations[] = 'Add more text content or reduce images';
        }

        // Hidden text or tiny text
        if (preg_match('/style="[^"]*font-size:\s*(1px|2px|3px|0px)/', $htmlContent) ||
            preg_match('/style="[^"]*color:\s*(white|#ffffff|#fff)\s*.*background-color:\s*(white|#ffffff|#fff)/', $htmlContent)) {
            $score += 3;
            $issues[] = 'Contains hidden or tiny text';
            $recommendations[] = 'Remove hidden text and ensure readable font sizes';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score HTML structure for spam indicators.
     */
    protected function scoreHtmlStructure(string $htmlContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        // Check for proper HTML structure
        if (!preg_match('/<!DOCTYPE[^>]*>/', $htmlContent)) {
            $score += 1;
            $warnings[] = 'Missing DOCTYPE declaration';
            $recommendations[] = 'Add proper DOCTYPE declaration';
        }

        if (strpos($htmlContent, '<html') === false) {
            $score += 1;
            $warnings[] = 'Missing HTML tag';
        }

        if (strpos($htmlContent, '<head>') === false) {
            $score += 1;
            $warnings[] = 'Missing HEAD section';
        }

        if (strpos($htmlContent, '<body>') === false) {
            $score += 1;
            $warnings[] = 'Missing BODY tag';
        }

        // Check for inline styles (can be spam indicator)
        $styleCount = substr_count($htmlContent, 'style=');
        if ($styleCount > 20) {
            $score += 1;
            $warnings[] = 'Heavy use of inline styles (' . $styleCount . ' instances)';
            $recommendations[] = 'Consider using CSS in head section';
        }

        // Check for table-based layouts (common in spam)
        $tableCount = substr_count($htmlContent, '<table');
        if ($tableCount > 10) {
            $score += 1;
            $warnings[] = 'Complex table-based layout';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score text content for spam indicators.
     */
    protected function scoreTextContent(string $text): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        // Character frequency analysis
        $totalChars = strlen($text);
        $upperCount = preg_match_all('/[A-Z]/', $text);
        $upperPercentage = $totalChars > 0 ? ($upperCount / $totalChars) * 100 : 0;

        if ($upperPercentage > 30) {
            $score += 2;
            $issues[] = 'High percentage of uppercase letters (' . round($upperPercentage, 1) . '%)';
            $recommendations[] = 'Reduce use of uppercase letters';
        }

        // Excessive punctuation
        $punctuationCount = preg_match_all('/[!?.,;:]/', $text);
        $punctuationPercentage = $totalChars > 0 ? ($punctuationCount / $totalChars) * 100 : 0;

        if ($punctuationPercentage > 10) {
            $score += 1;
            $warnings[] = 'High punctuation density';
        }

        // Repetitive characters
        if (preg_match('/(.)\1{4,}/', $text)) {
            $score += 2;
            $issues[] = 'Contains repetitive characters';
        }

        // Suspicious URLs
        if (preg_match('/(bit\.ly|tinyurl\.com|short\.link)/', $text)) {
            $score += 1;
            $warnings[] = 'Contains URL shorteners';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score sender information for spam indicators.
     */
    protected function scoreSenderInfo(array $options): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $fromName = trim((string) ($options['from_name'] ?? ''));
        $fromEmail = trim((string) ($options['from_email'] ?? ''));
        $replyToEmail = $options['reply_to'] ?? '';
        $deliveryServerType = strtolower(trim((string) ($options['delivery_server_type'] ?? '')));
        $deliveryServerFromEmail = trim((string) ($options['delivery_server_from_email'] ?? ''));
        $deliveryServerId = $options['delivery_server_id'] ?? null;
        $replyServerId = $options['reply_server_id'] ?? null;

        if ($fromName === '' && $fromEmail === '') {
            return [
                'score'           => 0,
                'issues'          => [],
                'warnings'        => [],
                'recommendations' => [],
                'pending'         => true,
                'incomplete'      => true,
                'incomplete_reasons' => ['Sender identity is missing.'],
                'pending_message' => 'Waiting for sender name and email to verify identity.',
            ];
        }

        if ($fromName === '') {
            $score += 15;
            $warnings[] = 'From name is empty';
            $recommendations[] = 'Set a recognizable sender name to improve trust.';
        }

        // Check from email domain
        if (!empty($fromEmail)) {
            $domain = substr(strrchr($fromEmail, '@'), 1);
            
            // Suspicious domains
            $suspiciousDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
            if (in_array($domain, $suspiciousDomains)) {
                $score += 10;
                $warnings[] = 'Using free email provider as sender';
                $recommendations[] = 'Use a business domain for sending';
            }

            // Numeric domains or suspicious patterns
            if (preg_match('/\d{3,}/', $domain)) {
                $score += 10;
                $issues[] = 'Domain contains multiple numbers';
            }
        }

        // Mismatch between from and reply-to
        if (!empty($fromEmail) && !empty($replyToEmail) && $fromEmail !== $replyToEmail) {
            $fromDomain = substr(strrchr($fromEmail, '@'), 1);
            $replyDomain = substr(strrchr($replyToEmail, '@'), 1);
            
            if ($fromDomain !== $replyDomain) {
                $score += 5;
                $warnings[] = 'From and Reply-To domains differ';
            }
        }

        // Delivery/reply server context checks
        if (empty($deliveryServerId)) {
            $warnings[] = 'No specific delivery server selected';
            $recommendations[] = 'Use a stable, warmed-up delivery server when possible.';
        }

        if ($deliveryServerType === 'amazon-ses' && !empty($deliveryServerFromEmail) && !empty($fromEmail)) {
            if (strcasecmp($deliveryServerFromEmail, $fromEmail) !== 0) {
                $score += 5;
                $warnings[] = 'From email differs from SES server sender identity';
                $recommendations[] = 'Use the SES-verified sender identity configured on your delivery server.';
            }
        }

        if (!empty($replyToEmail) && empty($replyServerId)) {
            $warnings[] = 'Reply-To is set but no reply server selected';
            $recommendations[] = 'Attach a reply server to improve reply handling consistency.';
        }

        return [
            'score' => $score,
            'issues' => $issues,
            'warnings' => $warnings,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Score domain authentication signals (from-email domain quality, delivery server selection).
     */
    protected function scoreDomainAuthentication(array $options): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $fromEmail = trim((string) ($options['from_email'] ?? ''));
        $deliveryServerId = $options['delivery_server_id'] ?? null;
        $deliveryServerType = strtolower(trim((string) ($options['delivery_server_type'] ?? '')));

        if (empty($fromEmail)) {
            return [
                'score'           => 0,
                'issues'          => [],
                'warnings'        => [],
                'recommendations' => [],
                'pending'         => true,
                'incomplete'      => true,
                'incomplete_reasons' => ['From email is missing for domain authentication.'],
                'pending_message' => 'Waiting for a From email address to check domain authentication.',
            ];
        }

        $atPos = strrpos($fromEmail, '@');
        $domain = $atPos !== false ? substr($fromEmail, $atPos + 1) : '';

        $freeDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com', 'aol.com', 'icloud.com'];
        if (in_array(strtolower($domain), $freeDomains, true)) {
            $score += 30;
            $issues[] = 'Sending from a free email provider (' . $domain . ') — most spam filters penalise this.';
            $recommendations[] = 'Use a business domain with SPF/DKIM records configured for reliable delivery.';
        } elseif ($domain !== '') {
            $recommendations[] = 'Ensure SPF and DKIM records are published for ' . $domain . ' to maximise inbox placement.';
        }

        if (preg_match('/\d{3,}/', $domain)) {
            $score += 10;
            $issues[] = 'Sending domain contains many numeric characters, which is a spam signal.';
        }

        if (empty($deliveryServerId)) {
            $score += 30;
            $warnings[] = 'No delivery server selected — system will auto-select one.';
            $recommendations[] = 'Choose a warmed-up, dedicated delivery server to improve domain reputation.';
            $incomplete = true;
        } else {
            $incomplete = false;
        }

        if ($deliveryServerType === 'amazon-ses') {
            $recommendations[] = 'Verify that DKIM signing is enabled in your SES sending identity settings.';
        }

        if ($incomplete) {
            return array_merge(compact('score', 'issues', 'warnings', 'recommendations'), [
                'incomplete' => true,
                'incomplete_reasons' => ['Delivery server is not selected.'],
            ]);
        }

        return compact('score', 'issues', 'warnings', 'recommendations');
    }

    /**
     * Score link quality — HTTPS usage, URL shorteners, suspicious domains.
     */
    protected function scoreLinkQuality(string $htmlContent, string $textContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $allText = $htmlContent . ' ' . $textContent;

        if (trim(strip_tags($allText)) === '') {
            return [
                'score' => 0,
                'issues' => [],
                'warnings' => [],
                'recommendations' => [],
                'pending' => true,
                'pending_message' => 'Waiting for email content to analyze links and URL quality.',
            ];
        }

        $shorteners = ['bit.ly', 'tinyurl.com', 'short.link', 'ow.ly', 't.co', 'goo.gl', 'rebrand.ly', 'cutt.ly'];
        foreach ($shorteners as $shortener) {
            if (stripos($allText, $shortener) !== false) {
                $score += 5;
                $warnings[] = 'Contains URL shortener (' . $shortener . ') — shorteners reduce trust.';
                $recommendations[] = 'Use full direct URLs instead of URL shorteners.';
                break;
            }
        }

        if ($htmlContent !== '') {
            preg_match_all('/href=["\']([^"\']+)["\']/', $htmlContent, $hrefMatches);
            $hrefs = $hrefMatches[1] ?? [];
            $httpCount = 0;
            foreach ($hrefs as $href) {
                if (preg_match('/^http:\/\//i', $href)) {
                    $httpCount++;
                }
            }
            if ($httpCount > 0) {
                $score += 5;
                $warnings[] = $httpCount . ' link(s) use HTTP instead of HTTPS.';
                $recommendations[] = 'Switch all links to HTTPS for security and better deliverability.';
            } elseif (!empty($hrefs)) {
                $recommendations[] = 'All links use HTTPS — good for trust and deliverability.';
            }
        }

        return compact('score', 'issues', 'warnings', 'recommendations');
    }

    /**
     * Score image-to-text ratio.
     */
    protected function scoreImageToTextRatio(string $htmlContent): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $imageCount = substr_count($htmlContent, '<img');
        $textLength = strlen(trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent))));

        if ($imageCount === 0 && $textLength === 0) {
            return [
                'score' => 0,
                'issues' => [],
                'warnings' => [],
                'recommendations' => [],
                'pending' => true,
                'pending_message' => 'Waiting for email content to evaluate image-to-text balance.',
            ];
        }

        if ($imageCount > 0 && $textLength < 50) {
            $score += 5;
            $issues[] = 'Email is almost entirely images with very little text.';
            $recommendations[] = 'Add more text content — aim for at least 60% text to avoid spam filters.';
        } elseif ($imageCount > 0 && $textLength < 200) {
            $score += 5;
            $warnings[] = 'Your email contains large images but little text. Add more text to avoid spam filters.';
            $recommendations[] = 'Increase the text-to-image ratio for better inbox placement.';
        } elseif ($imageCount > 5 && $textLength < 400) {
            $score += 5;
            $warnings[] = 'High image count relative to text length.';
        } elseif ($imageCount === 0 && $textLength > 0) {
            $recommendations[] = 'No images detected — text-only emails have excellent deliverability.';
        } else {
            $recommendations[] = 'Image-to-text ratio looks balanced.';
        }

        return compact('score', 'issues', 'warnings', 'recommendations');
    }

    /**
     * Score overall content quality — body text, HTML structure, text patterns, placeholder detection.
     */
    protected function scoreContentQuality(string $htmlContent, string $textContent, string $allText): array
    {
        $score = 0;
        $issues = [];
        $warnings = [];
        $recommendations = [];

        $plainFromHtml = trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent)));
        $plainText = trim(preg_replace('/\s+/', ' ', $plainFromHtml . ' ' . $textContent));
        $textLower = strtolower($plainText);

        if ($plainText === '') {
            return [
                'score'           => 0,
                'issues'          => [],
                'warnings'        => [],
                'recommendations' => [],
                'pending'         => true,
                'incomplete'      => true,
                'incomplete_reasons' => ['Email body content is missing.'],
                'pending_message' => 'Waiting for email body content to evaluate quality.',
            ];
        }

        if (strlen($plainText) < 40) {
            $score += 10;
            $warnings[] = 'Email content is very short.';
            $recommendations[] = 'Add more body copy so subscribers have clear context and intent.';
        }

        $placeholderPhrases = ['lorem ipsum', 'your content here', 'start writing', 'drag and drop', 'double click to edit'];
        foreach ($placeholderPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 10;
                $warnings[] = 'Email content appears to include placeholder text.';
                $recommendations[] = 'Replace placeholder text with your final campaign copy.';
                break;
            }
        }

        $spammyPhrases = [
            'click here', 'order now', 'buy now', 'limited time', 'act now', 'don\'t delete',
            'free money', 'cash bonus', 'risk free', 'no obligation', 'special promotion',
            'exclusive offer', 'once in a lifetime', 'urgent', 'immediate action required',
        ];
        foreach ($spammyPhrases as $phrase) {
            if (strpos($textLower, $phrase) !== false) {
                $score += 10;
                $issues[] = "Content contains spammy phrase: '{$phrase}'.";
                break;
            }
        }

        if (preg_match('/style="[^"]*font-size:\s*(0px|1px|2px|3px)/', $htmlContent)
            || preg_match('/style="[^"]*color:\s*(white|#fff|#ffffff)[^"]*background(-color)?:\s*(white|#fff|#ffffff)/', $htmlContent)) {
            $score += 20;
            $issues[] = 'Contains hidden or invisible text.';
            $recommendations[] = 'Remove hidden text — spam filters flag it heavily.';
        }

        $totalChars = strlen($plainText);
        if ($totalChars > 0) {
            $upperCount = preg_match_all('/[A-Z]/', $plainText);
            $upperPct = ($upperCount / $totalChars) * 100;
            if ($upperPct > 30) {
                $score += 10;
                $issues[] = 'High percentage of uppercase letters (' . round($upperPct, 1) . '%).';
                $recommendations[] = 'Reduce use of uppercase text to avoid spam triggers.';
            }
        }

        if (preg_match('/(.)\1{4,}/', $plainText)) {
            $score += 10;
            $issues[] = 'Content contains repetitive characters (e.g. "!!!!").';
        }

        if ($htmlContent !== '' && !preg_match('/<!DOCTYPE/i', $htmlContent)) {
            $score += 5;
            $warnings[] = 'HTML is missing a DOCTYPE declaration.';
        }

        return compact('score', 'issues', 'warnings', 'recommendations');
    }

    /**
     * Get assessment based on score.
     */
    protected function getAssessment(int $deliverabilityScore): string
    {
        if ($deliverabilityScore >= 90) {
            return 'Excellent';
        }

        if ($deliverabilityScore >= 70) {
            return 'Good';
        }

        if ($deliverabilityScore >= 50) {
            return 'Risky';
        }

        return 'Poor';
    }

    /**
     * Get the threshold for blocking emails.
     */
    protected function getBlockingThreshold(): int
    {
        return (int) config('mailpurse.spam_scoring.blocking_threshold', 50);
    }

    /**
     * Convert internal score to a 0-100 percentage.
     */
    protected function toPercent(int $score): int
    {
        return (int) round(min(100, max(0, $score)));
    }

    /**
     * Get a UI tone for the risk summary.
     */
    protected function getRiskTone(int $deliverabilityScore): string
    {
        if ($deliverabilityScore >= 90) {
            return 'positive';
        }

        if ($deliverabilityScore >= 50) {
            return 'warning';
        }

        return 'danger';
    }

    /**
     * Cap a category score to its configured maximum weight.
     */
    protected function applyCategoryWeight(string $category, array $data): array
    {
        $weight = self::CATEGORY_WEIGHTS[$category] ?? 0;

        if (!empty($data['pending'])) {
            $data['score'] = 0;

            return $data;
        }

        $penalty = min($weight, max(0, (int) ($data['score'] ?? 0)));
        $data['score'] = max(0, $weight - $penalty);

        return $data;
    }

    /**
     * Build a normalized checks payload for UI rendering.
     */
    protected function buildCheck(string $label, array $data): array
    {
        if (!empty($data['pending'])) {
            return [
                'label'   => $label,
                'score'   => 0,
                'tone'    => 'pending',
                'remarks' => [['tone' => 'pending', 'text' => $data['pending_message'] ?? 'Waiting for input…']],
            ];
        }

        $issues          = $data['issues'] ?? [];
        $warnings        = $data['warnings'] ?? [];
        $recommendations = $data['recommendations'] ?? [];

        if (!empty($issues)) {
            $tone = 'danger';
        } elseif (!empty($warnings)) {
            $tone = 'warning';
        } else {
            $tone = 'positive';
        }

        $remarks = array_merge(
            array_map(fn ($text) => ['tone' => 'danger',   'text' => $text], $issues),
            array_map(fn ($text) => ['tone' => 'warning',  'text' => $text], $warnings),
            array_map(fn ($text) => ['tone' => 'positive', 'text' => $text], $recommendations)
        );

        if (empty($remarks)) {
            $remarks[] = ['tone' => 'positive', 'text' => 'No major spam trigger detected in this section.'];
        }

        return [
            'label'   => $label,
            'score'   => (int) ($data['score'] ?? 0),
            'tone'    => $tone,
            'remarks' => $remarks,
        ];
    }

    /**
     * Build an aggregated remarks list.
     */
    protected function buildRemarks(array $issues, array $warnings, array $recommendations): array
    {
        return array_merge(
            array_map(fn ($text) => ['tone' => 'danger', 'text' => $text], $issues),
            array_map(fn ($text) => ['tone' => 'warning', 'text' => $text], $warnings),
            array_map(fn ($text) => ['tone' => 'positive', 'text' => $text], $recommendations)
        );
    }

    /**
     * Check if email should be blocked based on spam score.
     */
    public function shouldBlockEmail(string $subject, string $htmlContent, string $textContent, array $options = []): bool
    {
        $result = $this->calculateSpamScore($subject, $htmlContent, $textContent, $options);
        return $result['should_block'];
    }

    /**
     * Get quick spam score (0-100 scale).
     */
    public function getQuickSpamScore(string $subject, string $htmlContent, string $textContent): int
    {
        $result = $this->calculateSpamScore($subject, $htmlContent, $textContent);

        return $this->toPercent($result['score']);
    }

    
    /**
     * Get check tone based on individual score.
     */
    protected function getCheckTone(int $score): string
    {
        if ($score === 0) {
            return 'positive';
        } elseif ($score <= 2) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    
    /**
     * Get score percentage for frontend display.
     */
    protected function getScorePercent(int $score): float
    {
        // Convert to 0-100 scale (assuming max internal score is 30)
        return min(100, max(0, ($score / 30) * 100));
    }
}
