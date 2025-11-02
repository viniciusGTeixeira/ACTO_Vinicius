@extends('layouts.auth')

@section('title', 'Bem-vindo de volta')
@section('subtitle', 'Entre com suas credenciais')

@section('content')
<form method="POST" action="{{ route('login') }}" id="loginForm">
    @csrf
    
    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" 
               class="form-control js-input @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}" 
               placeholder="seu@email.com"
               required 
               autofocus>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password" class="form-label">Senha</label>
        <input type="password" 
               class="form-control js-input @error('password') is-invalid @enderror" 
               id="password" 
               name="password" 
               placeholder="••••••••"
               required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember">
        <label class="form-check-label" for="remember">
            Lembrar de mim
        </label>
    </div>
    
    <button type="submit" class="btn btn-primary js-submit-btn">
        <span>Entrar</span>
    </button>
    
    <div class="auth-footer">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">
                Esqueceu sua senha?
            </a>
        @endif
    </div>
    
    @if (Route::has('register'))
        <div class="auth-footer" style="margin-top: 1rem;">
            <span style="color: #6b7280;">Não tem uma conta? </span>
            <a href="{{ route('register') }}">
                Registre-se
            </a>
        </div>
    @endif
</form>

@push('scripts')
<script>
$(document).ready(function() {
    // Input focus effects
    $('.js-input').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
    
    // Add ripple effect on button click
    $('.js-submit-btn').on('click', function(e) {
        const button = $(this);
        const ripple = $('<span class="ripple"></span>');
        
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ripple.css({
            left: x + 'px',
            top: y + 'px'
        });
        
        button.append(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
    
    // Form validation with shake effect
    $('#loginForm').on('submit', function(e) {
        const form = $(this);
        const email = $('#email');
        const password = $('#password');
        let isValid = true;
        
        // Basic client-side validation
        if (!email.val() || !email.val().includes('@')) {
            email.addClass('shake');
            setTimeout(() => email.removeClass('shake'), 500);
            isValid = false;
        }
        
        if (!password.val() || password.val().length < 3) {
            password.addClass('shake');
            setTimeout(() => password.removeClass('shake'), 500);
            isValid = false;
        }
        
        // Add loading state to button
        if (isValid) {
            const submitBtn = $('.js-submit-btn');
            submitBtn.prop('disabled', true);
            submitBtn.html('<span>Entrando...</span>');
        }
    });
    
    // Input typing effect
    $('.js-input').on('input', function() {
        const input = $(this);
        input.addClass('typing');
        
        clearTimeout(input.data('typingTimer'));
        input.data('typingTimer', setTimeout(function() {
            input.removeClass('typing');
        }, 500));
    });
    
    // Shake invalid inputs on page load
    @if($errors->any())
        $('.is-invalid').each(function() {
            const input = $(this);
            setTimeout(() => {
                input.addClass('shake');
                setTimeout(() => input.removeClass('shake'), 500);
            }, 100);
        });
    @endif
});
</script>

<style>
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        width: 20px;
        height: 20px;
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            width: 300px;
            height: 300px;
            opacity: 0;
            transform: translate(-50%, -50%);
        }
    }
    
    .typing {
        animation: typing-pulse 0.5s ease-in-out;
    }
    
    @keyframes typing-pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
    
    .focused .form-control {
        animation: focus-bounce 0.3s ease-out;
    }
    
    @keyframes focus-bounce {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
</style>
@endpush
@endsection

