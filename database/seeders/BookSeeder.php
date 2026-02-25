<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'name' => 'Luna (Unicorn)',
                'image' => 'uploads/books/images/luna.jpeg',
                'book_pdf' => 'uploads/books/pdfs/luna.pdf'
            ],
            [
                'name' => 'Oscar (Monkey)',
                'image' => 'uploads/books/images/oscar.jpeg',
                'book_pdf' => 'uploads/books/pdfs/oscar.pdf'
            ],
            [
                'name' => 'Lily (Fox)',
                'image' => 'uploads/books/images/lily.jpeg',
                'book_pdf' => 'uploads/books/pdfs/lily.pdf'
            ],
            [
                'name' => 'Rocket (Dog)',
                'image' => null,
                'book_pdf' => 'uploads/books/pdfs/rocket.pdf'
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
