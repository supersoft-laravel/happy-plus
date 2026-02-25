<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view book');
        try {
            $books = Book::all();
            return view('dashboard.books.index', compact('books'));
        } catch (\Throwable $th) {
            Log::error('Books Index Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create book');
        try {
            return view('dashboard.books.create');
        } catch (\Throwable $th) {
            Log::error('Books Create Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create book');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max_size',
            'book_pdf' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            Log::error('Book Validation Failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            $book = new Book();
            $book->name = $request->name;

            if ($request->hasFile('image')) {
                $Image = $request->file('image');
                $Image_ext = $Image->getClientOriginalExtension();
                $Image_name = time() . '_image.' . $Image_ext;

                $Image_path = 'uploads/books/images';
                $Image->move(public_path($Image_path), $Image_name);
                $book->image = $Image_path . "/" . $Image_name;
            }

            if ($request->hasFile('book_pdf')) {
                $Image = $request->file('book_pdf');
                $Image_ext = $Image->getClientOriginalExtension();
                $Image_name = time() . '_book_pdf.' . $Image_ext;

                $Image_path = 'uploads/books/pdfs';
                $Image->move(public_path($Image_path), $Image_name);
                $book->book_pdf = $Image_path . "/" . $Image_name;
            }

            $book->save();

            DB::commit();
            return redirect()->route('dashboard.books.index')->with('success', 'Book Created Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Book Store Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
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
        $this->authorize('update book');
        try {
            $book = Book::findOrFail($id);
            return view('dashboard.books.edit', compact('book'));
        } catch (\Throwable $th) {
            Log::error('Book Edit Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update book');
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max_size',
            'book_pdf' => 'nullable|file|mimes:pdf',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }
        try {
            $book = Book::findOrFail($id);
            $book->name = $request->name;

            if ($request->hasFile('image')) {
                if (isset($book->image) && File::exists(public_path($book->image))) {
                    File::delete(public_path($book->image));
                }
                $Image = $request->file('image');
                $Image_ext = $Image->getClientOriginalExtension();
                $Image_name = time() . '_image.' . $Image_ext;

                $Image_path = 'uploads/books/images';
                $Image->move(public_path($Image_path), $Image_name);
                $book->image = $Image_path . "/" . $Image_name;
            }

            if ($request->hasFile('book_pdf')) {
                if (isset($book->book_pdf) && File::exists(public_path($book->book_pdf))) {
                    File::delete(public_path($book->book_pdf));
                }
                $Image = $request->file('book_pdf');
                $Image_ext = $Image->getClientOriginalExtension();
                $Image_name = time() . '_book_pdf.' . $Image_ext;

                $Image_path = 'uploads/books/pdfs';
                $Image->move(public_path($Image_path), $Image_name);
                $book->book_pdf = $Image_path . "/" . $Image_name;
            }

            $book->save();

            return redirect()->route('dashboard.books.index')->with('success', 'Book Updated Successfully');
        } catch (\Throwable $th) {
            Log::error('Book Update Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete book');
        try {
            $book = Book::findOrFail($id);
            if (isset($book->image) && File::exists(public_path($book->image))) {
                File::delete(public_path($book->image));
            }
            if (isset($book->book_pdf) && File::exists(public_path($book->book_pdf))) {
                File::delete(public_path($book->book_pdf));
            }
            $book->delete();
            return redirect()->back()->with('success', 'Book Deleted Successfully');
        } catch (\Throwable $th) {
            Log::error('Book Delete Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    public function updateStatus(string $id)
    {
        $this->authorize('update book');
        try {
            $book = Book::findOrFail($id);
            $message = $book->is_active == 'active' ? 'Book Deactivated Successfully' : 'Book Activated Successfully';
            if ($book->is_active == 'active') {
                $book->is_active = 'inactive';
                $book->save();
            } else {
                $book->is_active = 'active';
                $book->save();
            }
            return redirect()->back()->with('success', $message);
        } catch (\Throwable $th) {
            Log::error('Book Status Updation Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }
}
