<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <-- Import Storage
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Menampilkan daftar semua proyek.
     */
    public function index()
    {
        $projects = Project::with('user') // Eager load pembuat
                            ->latest()
                            ->paginate(15);
                            
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Tampilkan form edit moderasi.
     */
    public function edit(Project $project)
    {
        $project->load('user');
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Update status, visibility, dan featured.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['draft', 'review', 'published', 'archived'])],
            'visibility' => ['required', 'string', Rule::in(['public', 'university_only', 'private'])],
            'featured' => 'nullable|boolean', // 'featured' akan jadi '1' atau null
        ]);

        // Konversi 'featured'
        // Jika checkbox dicentang, $request->featured akan '1'
        // Jika tidak dicentang, $request->featured akan null
        $dataToUpdate = [
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'featured' => $request->has('featured') ? true : false,
        ];
        
        $project->update($dataToUpdate);

        return redirect()->route('admin.projects.index')
                         ->with('success', 'Moderasi proyek berhasil diperbarui.');
    }

    /**
     * Hapus proyek secara permanen.
     */
    public function destroy(Project $project)
    {
        // Hapus file-file terkait (thumbnail, images)
        if ($project->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }
        
        // (Nanti jika ada project_images, hapus juga di sini)
        // foreach ($project->images as $image) {
        //     Storage::disk('public')->delete($image->image_path);
        // }

        $project->delete(); // Ini akan cascade ke likes, bookmarks, dll.

        return redirect()->route('admin.projects.index')
                         ->with('success', 'Proyek berhasil dihapus permanen.');
    }
}