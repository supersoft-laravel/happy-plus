<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            // Base Query (Only role = user)
            $usersQuery = User::role('user');

            // Total Users (excluding soft deleted)
            $totalUsers = (clone $usersQuery)->count();

            // Active Users
            $activeUsers = (clone $usersQuery)
                ->where('is_active', 1)
                ->count();

            // Inactive Users
            $inactiveUsers = (clone $usersQuery)
                ->where('is_active', 0)
                ->count();

            // Archived Users (Soft Deleted)
            $archivedUsers = User::onlyTrashed()
                ->role('user')
                ->count();

            // New Users This Month
            $monthlyUsers = (clone $usersQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            return view('dashboard.index', compact(
                'totalUsers',
                'activeUsers',
                'inactiveUsers',
                'archivedUsers',
                'monthlyUsers'
            ));

        } catch (\Throwable $th) {

            Log::error('Dashboard Index Failed', [
                'error' => $th->getMessage()
            ]);

            return redirect()->back()->with(
                'error',
                "Something went wrong! Please try again later"
            );
        }
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
        //
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
    public function destroy(string $id)
    {
        //
    }
}
