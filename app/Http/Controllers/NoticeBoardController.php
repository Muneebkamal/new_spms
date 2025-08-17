<?php

namespace App\Http\Controllers;

use App\Models\NoticeBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NoticeBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notices = NoticeBoard::all();
        return view('notice-board.index', compact('notices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $noticeboard = NoticeBoard::create([
            'date' => $request->date,
            'description' => $request->description,
            'link' => $request->link,
            'files' => null,
            'remark' => $request->remark,
        ]);

        $files = [];

        if ($request->hasFile('image')) {
            $folderPath = public_path("assets/noticeboard/{$noticeboard->id}");

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            foreach ($request->file('image') as $file) {
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->move($folderPath, $filename);
                $files[] = "assets/noticeboard/{$noticeboard->id}/{$filename}";
            }
        }

        $noticeboard->update([
            'files' => implode(',', $files),
        ]);

        return redirect()->back()->with('success', 'Notice added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notice = Noticeboard::findOrFail($id);
        $folderPath = public_path("assets/noticeboard/{$id}");

        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $notice->delete();

        return redirect()->route('notice.board.index')->with('success', 'Notice deleted successfully.');
    }
}
