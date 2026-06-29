<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Training;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $trainings = Training::published()
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('pages.training.index', compact('trainings'));
    }

    public function show(Training $training): View
    {
        abort_if(! $training->isPublished(), 404);

        $training->load('media');

        $related = Training::published()
            ->where('id', '!=', $training->id)
            ->where('category', $training->category)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('pages.training.show', compact('training', 'related'));
    }
}
