@extends('layouts.auth')

@section('title', 'Criar Conta')
@section('subtitle', 'Comece sua jornada')

@section('content')
<form method="POST" action="{{ route('register') }}" id="registerForm">
    @csrf
    
    <div class="form-group">
        <label for="name" class="form-label">Nome Completo</label>
        <input type="text" 
               class="form-control js-input @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name') }}" 
               placeholder="João Silva"
               required 
               autofocus>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input type="email" 
               class="form-control js-input @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email') }}" 
               placeholder="seu@email.com"
               required>
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
        @else
            <small style="color: #6b7280; font-size: 0.875rem;">Mínimo de 8 caracteres</small>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password_confirmation" class="form-label">Confirmar Senha</label>
        <input type="password" 
               class="form-control js-input" 
               id="password_confirmation" 
               name="password_confirmation" 
               placeholder="••••••••"
               required>
    </div>
    
    <button type="submit" class="btn btn-primary js-submit-btn">
        <span>Criar Conta</span>
    </button>
    
    <div class="auth-footer">
        <span style="color: #6b7280;">Já tem uma conta? </span>
        <a href="{{ route('login') }}">
            Faça login
        </a>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    $('.js-input').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
    
    $('.js-submit-btn').on('click', function(e) {
        const button = $(this);
        const ripple = $('<span class="ripple"></span>');
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ripple.css({ left: x + 'px', top: y + 'px' });
        button.append(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
    
    $('#registerForm').on('submit', function() {
        const submitBtn = $('.js-submit-btn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span>Criando conta...</span>');
    });
    
    $('.js-input').on('input', function() {
        const input = $(this);
        input.addClass('typing');
        clearTimeout(input.data('typingTimer'));
        input.data('typingTimer', setTimeout(() => input.removeClass('typing'), 500));
    });
    
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

