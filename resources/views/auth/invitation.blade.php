@php
    use App\Models\User;
    use Illuminate\Support\Facades\DB;
@endphp
@extends('layouts.master-without-nav')
@section('title')
    Convite
@endsection
@section('content')
    <div class="auth-page-wrapper pt-5">

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <div>
                                <a href="index" class="d-inline-block auth-logo">
                                    <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="{{appName()}}" height="39">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    //appPrintR($connectionCodeParts);
                    //$hostUserId = $connectionCodeParts[0] ?? request()->cookie('vistoriaplus_hostUserId');

                    $questUserEmailFromInvitation = $connectionCodeParts[1] ?? null;

                    if($questUserEmailFromInvitation){
                        $guestExists = DB::connection('vpOnboard')
                            ->table('users')
                            ->where('email', $questUserEmailFromInvitation)
                            ->first();
                        $questUserEmail = $guestExists->email ?? null;
                    }

                    //$questUserParams = $connectionCodeParts[2] ?? request()->cookie('vistoriaplus_questUserParams');
                @endphp

                @if ($hostUserId && $questUserParams && $questUserEmailFromInvitation)
                    @php
                        $hostUser = User::find($hostUserId);
                        $hostUserName = $hostUser->name ?? '';
                    @endphp
                    <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                        <i class="ri-check-double-line label-icon"></i>
                        Você redebeu um contive para colaborar com <strong class="text-theme">{{$hostUserName}}</strong>.<br>
                        @if (isset($questUserEmailFromInvitation))
                            O convite foi enviado para <u>{{$questUserEmailFromInvitation}}</u> . Utilize-o para registro ou informe outro.
                        @elseif(isset($questUserEmail))
                            O e-mail <u>{{$questUserEmail}}</u> já contacomo contaem nossa base de dados. Utileze-o para Login.
                        @else
                            Registre-se ou efetue Login.
                        @endif
                    </div>

                    {{--
                    @include('components.alerts')
                    --}}

                    <div class="row justify-content-center">

                        <div class="col-sm-12 col-md-6">
                            @include('auth.login-card')
                        </div>

                        <div class="col-sm-12 col-md-6">
                            @include('auth.register-card')
                        </div>

                    </div>
                @else
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mt-4" role="alert">
                        <i class="ri-error-warning-fill label-icon"></i>
                        O parâmetros deste convite estão incorretos.<br>Clique no link enviado ao seu e-mail ou solcitie seu convite novamente.
                    </div>
                @endif
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; {{date('Y')}} {{appName()}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->
@endsection
@section('script')
    <script src="{{ URL::asset('build/js/register.js') }}?v={{env('APP_VERSION')}}" type="module"></script>
@endsection
