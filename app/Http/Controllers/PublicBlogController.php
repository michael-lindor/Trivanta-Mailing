<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicBlogController extends Controller
{
    private function ensureBlogEnabled(): void
    {
        if (!(bool) Setting::get('blog_enabled', true)) {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $this->ensureBlogEnabled();

        $posts = BlogPost::query()
            ->where('status', 'publish')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('public.blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $this->ensureBlogEnabled();

        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', 'publish')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return view('public.blog.show', compact('post'));
    }
}
