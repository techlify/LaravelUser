@extends('email.layouts.app')

@section('title', 'Forgot Password')

@section('content')
        <tr>
            <td class="container">

                <table>
                    <tr>
                        <td class="padding-high">
                            <div class="line"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="line-space"></td>
                    </tr>
                    <tr>
                        <td class="padding-low">
                            <div class="line"></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="line-space"></td>
                    </tr>
                    <tr>
                        <td align="center" class="content">

                            <h3>Hi, {{ $user->name }}!</h3>

                        </td>
                    </tr>
                    <tr>
                        <td class="content" align="center">
                            <p>Your has been reset</p>
                            <p>Email: {{ $user->email }}</p>
                            <p>One-Time Password: {{ $password }}</p>

                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="content">

                            <h5>Please reply to this email if you have any questions. Welcome!</h5>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
@endsection
