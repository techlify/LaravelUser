@extends('email.layouts.app')

@section('title', 'Welcome to eBusiness')

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
                            <p>Welcome to our Business Management Platform. </p>
                            <p>An account has been created for you on https://ebusiness.gy. Please use the One-Time Password provided below to login. Remember to change your password after logging in. </p>
                            <p>Email: {{ $user->email }} <small>This email is used to login to https://eBusiness.gy</small></p>
                            <p>One-Time Password: {{ $password }}</p>

                        </td>
                    </tr>
                    <tr>
                        <td align="center" class="content">
                            <h5 style="margin-top: 0px;margin-bottom: 30px;">Introduction about our App!</h5>
                            <a href="{{ env('INTRO_VIDEO_URL') }}" target="_blank">
                                <img src="{{ env('INTRO_VIDEO_THUMNAIL_URL') }}" height="250px" width="400px">
                            </a>
                        </td>
                    </tr>
                    @if (count($modules) > 0)
                        <tr>
                            <td align="center" class="content">
                                <p >eBusiness is composed of several modules, claim your free 14-day trial for each module and decide whether each is useful to your business</p>
                                <table border=1>
                                    <tr>
                                        <td align="center">
                                            <h6 style="margin:5px">Module</h6>
                                        </td>
                                        <td align="center">
                                            <h6 style="margin:5px">description</h6>
                                        </td>
                                    </tr>
                                    @foreach ($modules as $module)
                                    <tr>
                                        <td align="center">{{ $module->name }}</td>
                                        <td align="center">{{ $module->description }}</td>
                                    </tr>
                                    @endforeach
                                </table>   
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td align="center" class="content">

                            <h5>Please reply to this email if you have any questions. Welcome!</h5>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
@endsection