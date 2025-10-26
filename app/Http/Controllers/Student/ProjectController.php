<?php

namespace App\Http\Controllers\Student;

use App\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Technology;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Notifications\ProjectSubmittedForReview;
use Illuminate\Support\Facades\Notification;


class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->latest()->paginate(10);
        return view('student.projects.index', compact('projects'));
    }

    private function getFormData()
    {
        return [
            'lecturers' => User::where('role', 'lecturer')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'technologies' => Technology::orderBy('name')->get(),
        ];
    }

    public function create()
    {
        $formData = $this->getFormData();
        $project = new Project();
        $project->categories = collect();
        $project->technologies = collect();
        $project->images = collect();
        
        return view('student.projects.create', array_merge($formData, ['project' => $project]));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'demo_url' => 'nullable|url|max:255',
            'repository_url' => 'nullable|url|max:255',
            'status' => ['required', Rule::in(['draft', 'review'])],
            'visibility' => ['required', Rule::in(['public', 'university_only', 'private'])],
            'supervisor_id' => 'nullable|exists:users,id',
            'semester' => 'nullable|integer|min:1|max:14',
            'academic_year' => 'nullable|string|max:10',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'technologies' => 'nullable|array',
            'technologies.*' => 'exists:technologies,id',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'collaborators' => 'nullable|array',
            'collaborators.*' => 'exists:users,id',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();

        $project = Project::create($validated);

        $collaborators = $request->input('collaborators', []);
        $collaborators[] = Auth::id();
        $project->collaborators()->sync(array_unique($collaborators));

        if (!empty($validated['categories'])) {
            $project->categories()->attach($validated['categories']);
        }
        if (!empty($validated['technologies'])) {
            $project->technologies()->attach($validated['technologies']);
        }

        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('screenshots', 'public');
                $project->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        if ($validated['status'] === 'review') {
            $lecturers = User::where('role', 'lecturer')->get();
            Notification::send($lecturers, new ProjectSubmittedForReview($project));
        }

        return redirect()->route('student.projects.index')
                         ->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function show(Project $project)
    {
        return redirect()->route('student.projects.edit', $project);
    }

    public function edit(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'ANDA TIDAK BERHAK MENGEDIT PROYEK INI');
        }

        $formData = $this->getFormData();

        $project->load('categories', 'technologies', 'images');

        return view('student.projects.edit', array_merge($formData, ['project' => $project]));
    }

    public function update(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'ANDA TIDAK BERHAK MENGEDIT PROYEK INI');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'demo_url' => 'nullable|url|max:255',
            'repository_url' => 'nullable|url|max:255',
            'status' => ['required', Rule::in(['draft', 'review'])],
            'visibility' => ['required', Rule::in(['public', 'university_only', 'private'])],
            'supervisor_id' => 'nullable|exists:users,id',
            'semester' => 'nullable|integer|min:1|max:14',
            'academic_year' => 'nullable|string|max:10',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'technologies' => 'nullable|array',
            'technologies.*' => 'exists:technologies,id',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'collaborators' => 'nullable|array',
            'collaborators.*' => 'exists:users,id',
        ]);

        $notifyLecturers = ($project->status !== 'review' && $validated['status'] === 'review');



        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        $project->update($validated);

        $collaborators = $request->input('collaborators', []);
        $collaborators[] = Auth::id();
        $project->collaborators()->sync(array_unique($collaborators));

        $project->categories()->sync($validated['categories'] ?? []);
        $project->technologies()->sync($validated['technologies'] ?? []);
        
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('screenshots', 'public');
                $project->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        if ($notifyLecturers) {
            $lecturers = User::where('role', 'lecturer')->get();
            Notification::send($lecturers, new ProjectSubmittedForReview($project));
        }
        return redirect()->route('student.projects.index')
                         ->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'ANDA TIDAK BERHAK MENGHAPUS PROYEK INI');
        }
        
        if ($project->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }

        foreach ($project->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $project->delete();

        return redirect()->route('student.projects.index')
                         ->with('success', 'Proyek berhasil dihapus.');
    }
}
