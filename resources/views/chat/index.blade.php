@extends('layouts.master-without-nav')
@section('title')
    @lang('translation.chat')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/glightbox/css/glightbox.min.css') }}">
@endsection
@section('content')
    <div class="chat-wrapper d-lg-flex gap-1 p-1">
        <div class="chat-leftsidebar minimal-border">
            <div class="px-4 pt-4 mb-3">
                <!--
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1">
                        <h5 class="mb-4">Chats</h5>
                    </div>
                    <div class="flex-shrink-0">
                        <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Add Contact">
                            <button type="button" class="btn btn-soft-success btn-sm material-shadow-none">
                                <i class="ri-add-line align-bottom"></i>
                            </button>
                        </div>
                    </div>
                </div>
                -->
                <div class="search-box">
                    <input id="searchMemberList" type="text" class="form-control bg-light border-light" placeholder="Buscar por nome...">
                    <i class="ri-search-2-line search-icon"></i>
                </div>
            </div> <!-- .p-4 -->

            {{--
            <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#chats" role="tab">
                        Chats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#contacts" role="tab">
                        Contacts
                    </a>
                </li>
            </ul>
            --}}

            <div class="tab-content text-muted">
                <div class="tab-pane active" id="chats" role="tabpanel">
                    <div class="chat-room-list pt-3 overflow-y-auto">
                        <!--
                        <div class="d-flex align-items-center px-4 mb-2">
                            <div class="flex-grow-1">
                                <h4 class="mb-0 fs-11 text-muted text-uppercase">Direct Messages</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                    title="New Message">

                                    <button type="button" class="btn btn-soft-success btn-sm shadow-none material-shadow">
                                        <i class="ri-add-line align-bottom"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        -->

                        <div class="chat-message-list">
                            <ul id="userList" class="list-unstyled chat-list chat-user-list">
                                @if ($users->isNotEmpty())
                                    @foreach ($users as $key => $user)
                                        @php
                                            $avatarURL = checkUserAvatar($user->avatar);
                                        @endphp
                                        <li data-name="direct-message" data-search-user-id="{{$user->id}}" data-search-user-name="{{$user->name}}">
                                            <input id="contact-id-{{$user->id}}" name="user_chat" type="radio" value="{{$user->id}}"
                                            data-user-id="{{$user->id}}"
                                            data-user-name="{{$user->name}}"
                                            data-user-avatar="{{$avatarURL}}"
                                            class="d-none">
                                            <label for="contact-id-{{$user->id}}" title="{{$user->name}}" class="cursor-pointer">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img align-self-center me-2 ms-0">
                                                        <div class="avatar-xxs">
                                                            <img src="{{$avatarURL}}" class="rounded-circle img-fluid userprofile" alt="{{$user->name}}" width="24" height="24">
                                                            <span class="user-status" data-user-id="{{$user->id}}"></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <p class="text-truncate mb-0">{{limitChars($user->name, 25)}}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>

                        {{--
                        <div class="d-flex align-items-center px-4 mt-4 pt-2 mb-2">
                            <div class="flex-grow-1">
                                <h4 class="mb-0 fs-11 text-muted text-uppercase">Channels</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                    title="Create group">
                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-soft-success btn-sm">
                                        <i class="ri-add-line align-bottom"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="chat-message-list">
                            <ul class="list-unstyled chat-list chat-user-list mb-0" id="channelList">
                            </ul>
                        </div>
                        --}}

                        <!-- End chat-message-list -->
                    </div>
                </div>
                {{--
                <div class="tab-pane" id="contacts" role="tabpanel">
                    <div class="chat-room-list pt-3" data-simplebar>
                        <div class="sort-contact">
                        </div>
                    </div>
                </div>
                --}}
            </div>
            <!-- end tab contact -->
        </div>
        <!-- end chat leftsidebar -->
        <!-- Start User chat -->
        <div class="user-chat w-100 overflow-hidden minimal-border">

            <div class="chat-content d-lg-flex">
                <!-- start chat conversation section -->
                <div class="w-100 overflow-hidden position-relative">
                    <!-- conversation user -->
                    <div class="position-relative">

                        <div id="users-chat" class="position-relative">
                            <div class="p-3 user-chat-topbar d-none">
                                <div class="row align-items-center">
                                    <div class="col-sm-4 col-8">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i class="ri-arrow-left-s-line align-bottom"></i></a>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                        <img id="useravatar" src="{{ URL::asset('build/images/users/user-dummy-img.jpg') }}" class="rounded-circle avatar-xs" alt="">
                                                        {{--
                                                        <span class="user-status"></span>
                                                        --}}
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate mb-0 fs-16">
                                                            <span id="username" class="text-reset username"></span>
                                                        </h5>
                                                        {{--
                                                        <p class="text-truncate text-muted fs-14 mb-0 userStatus">
                                                            <small>Online</small>
                                                        </p>
                                                        --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-8 col-4">
                                        <ul class="list-inline user-chat-nav text-end mb-0">
                                            <li class="list-inline-item m-0">
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-secondary btn-icon material-shadow-none"
                                                        type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i data-feather="search" class="icon-sm"></i>
                                                    </button>
                                                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                        <div class="p-2">
                                                            <div class="search-box">
                                                                <input type="text"
                                                                    class="form-control bg-light border-light"
                                                                    placeholder="Search here..."
                                                                    onkeyup="searchMessages()" id="searchMessage">
                                                                <i class="ri-search-2-line search-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-inline-item d-none d-lg-inline-block m-0">
                                                <button type="button"
                                                    class="btn btn-ghost-secondary btn-icon material-shadow-none"
                                                    data-bs-toggle="offcanvas" data-bs-target="#userProfileCanvasExample"
                                                    aria-controls="userProfileCanvasExample">
                                                    <i data-feather="info" class="icon-sm"></i>
                                                </button>
                                            </li>

                                            <li class="list-inline-item m-0">
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-secondary btn-icon material-shadow-none"
                                                        type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i data-feather="more-vertical" class="icon-sm"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item d-block d-lg-none user-profile-show"
                                                            href="#"><i
                                                                class="ri-user-2-fill align-bottom text-muted me-2"></i>
                                                            View Profile</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-inbox-archive-line align-bottom text-muted me-2"></i>
                                                            Archive</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-mic-off-line align-bottom text-muted me-2"></i>
                                                            Muted</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>
                                                            Delete</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <!-- end chat user head -->
                            <div id="chat-conversation" class="chat-conversation p-3 p-lg-4 overflow-y-scroll">
                                <ul id="users-conversation" class="list-unstyled chat-conversation-list">

                                    <li class="position-absolute top-50 start-50 translate-middle d-block">
                                        <div class="text-center">
                                            <img class="rounded-circle img-thumbnail" src="{{checkUserAvatar($currentUser->avatar)}}" width="100" height="100">
                                        </div>
                                        <div class="mt-2 text-center fs-20">
                                            {{$currentUser->name}},<br>Olá!
                                        </div>
                                    </li>

                                </ul>
                                <!-- end chat-conversation-list -->
                            </div>

                        </div>

                        {{--
                        <div class="position-relative" id="channel-chat">
                            <div class="p-3 user-chat-topbar">
                                <div class="row align-items-center">
                                    <div class="col-sm-4 col-8">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i
                                                        class="ri-arrow-left-s-line align-bottom"></i></a>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex align-items-center">
                                                    <div
                                                        class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">
                                                        <img src="{{ URL::asset('build/images/users/avatar-2.jpg') }}"
                                                            class="rounded-circle avatar-xs" alt="">
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <h5 class="text-truncate mb-0 fs-16"><a
                                                                class="text-reset username" data-bs-toggle="offcanvas"
                                                                href="#userProfileCanvasExample"
                                                                aria-controls="userProfileCanvasExample">Lisa Parker</a>
                                                        </h5>
                                                        <p class="text-truncate text-muted fs-14 mb-0 userStatus"><small>24
                                                                Members</small></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-8 col-4">
                                        <ul class="list-inline user-chat-nav text-end mb-0">
                                            <li class="list-inline-item m-0">
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i data-feather="search" class="icon-sm"></i>
                                                    </button>
                                                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                        <div class="p-2">
                                                            <div class="search-box">
                                                                <input type="text"
                                                                    class="form-control bg-light border-light"
                                                                    placeholder="Search here..."
                                                                    onkeyup="searchMessages()" id="searchMessage">
                                                                <i class="ri-search-2-line search-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>

                                            <li class="list-inline-item d-none d-lg-inline-block m-0">
                                                <button type="button" class="btn btn-ghost-secondary btn-icon"
                                                    data-bs-toggle="offcanvas" data-bs-target="#userProfileCanvasExample"
                                                    aria-controls="userProfileCanvasExample">
                                                    <i data-feather="info" class="icon-sm"></i>
                                                </button>
                                            </li>

                                            <li class="list-inline-item m-0">
                                                <div class="dropdown">
                                                    <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i data-feather="more-vertical" class="icon-sm"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item d-block d-lg-none user-profile-show"
                                                            href="#"><i
                                                                class="ri-user-2-fill align-bottom text-muted me-2"></i>
                                                            View Profile</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-inbox-archive-line align-bottom text-muted me-2"></i>
                                                            Archive</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-mic-off-line align-bottom text-muted me-2"></i>
                                                            Muted</a>
                                                        <a class="dropdown-item" href="#"><i
                                                                class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>
                                                            Delete</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                            <!-- end chat user head -->
                            <div class="chat-conversation p-3 p-lg-4" id="chat-conversation" data-simplebar>
                                <ul class="list-unstyled chat-conversation-list" id="channel-conversation">
                                </ul>
                                <!-- end chat-conversation-list -->

                            </div>
                            <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show "
                                id="copyClipBoardChannel" role="alert">
                                Message copied
                            </div>
                        </div>
                        --}}

                        <!-- end chat-conversation -->

                        <div class="chat-input-section p-3 p-lg-4 d-none">

                            <form id="chatinput-form" enctype="multipart/form-data">
                                <div class="row g-0 align-items-center">
                                    <div class="col-auto">
                                        <div class="chat-input-links me-2">
                                            <div class="links-list-item">
                                                <button type="button" class="btn btn-link text-decoration-none emoji-btn" id="emoji-btn">
                                                    <i class="bx bx-smile align-middle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div id="chat-input-feedback" class="chat-input-feedback"></div>
                                        <textarea id="message-textarea" class="form-control chat-input bg-light border-light" placeholder="Mensagem..." autocomplete="off" style="height: 60px !important"></textarea>
                                    </div>
                                    <div class="col-auto">
                                        <div class="chat-input-links ms-2">
                                            <div class="links-list-item">
                                                <button id="send-message" type="button" class="btn btn-theme chat-send ri-send-plane-2-fill" style="height: 60px !important">
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{--
                        <div class="replyCard">
                            <div class="card mb-0">
                                <div class="card-body py-3">
                                    <div class="replymessage-block mb-0 d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="conversation-name"></h5>
                                            <p class="mb-0"></p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <button type="button" id="close_toggle"
                                                class="btn btn-sm btn-link mt-n2 me-n3 fs-18">
                                                <i class="bx bx-x align-middle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end chat-wrapper-->
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>

    {{--
    <script src="{{ URL::asset('build/libs/laravel-echo/echo.js') }}?v={{env('APP_VERSION')}}"></script>
    <script src="{{ URL::asset('build/libs/pusher-js/web/pusher.min.js') }}?v={{env('APP_VERSION')}}"></script>
    <script type="module">
    const Echo = window.Echo;
    const Pusher = window.Pusher;
    </script>
    --}}
    <script>
        var chatRetrieveURL = "{{route('chatRetrieveURL')}}";
        var chatStoreURL = "{{route('chatStoreURL')}}";
    </script>
    <script src="{{ URL::asset('build/js/echo.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
