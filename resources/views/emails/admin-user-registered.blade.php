@extends('layouts.mails.master')

@section('title', 'New User Registered')

@section('content')

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fb;padding:20px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;padding:25px;font-family:Arial,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                <tr>
                    <td style="text-align:center;padding-bottom:15px;">
                        <h2 style="margin:0;color:#2c3e50;">🎉 New User Registered</h2>
                        <p style="margin:5px 0;color:#777;font-size:14px;">
                            A new user has joined your platform.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
                            <tr>
                                <td style="font-weight:bold;width:160px;">👤 Name</td>
                                <td>{{ $user->name }}</td>
                            </tr>

                            <tr style="background:#f9fafc;">
                                <td style="font-weight:bold;">📧 Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold;">🆔 Username</td>
                                <td>{{ $user->username }}</td>
                            </tr>

                            <tr style="background:#f9fafc;">
                                <td style="font-weight:bold;">🌍 Signup Source</td>
                                <td>
                                    @if($user->provider)
                                        <span style="color:#0d6efd;font-weight:bold;">
                                            {{ ucfirst($user->provider) }} Login
                                        </span>
                                    @else
                                        <span style="color:#198754;font-weight:bold;">
                                            Direct Signup
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td style="font-weight:bold;">⏰ Registered At</td>
                                <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding-top:20px;text-align:center;color:#999;font-size:12px;">
                        This is an automated notification from {{ config('app.name') }}.
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

@endsection
