@extends('layouts.auth')

@section('title', 'Verificação 2FA')
@section('subtitle', 'Proteja sua conta')

@section('content')
<div style="margin-bottom: 1.5rem; padding: 1rem; background: #f0f9ff; border-radius: 12px; border-left: 4px solid #00c853;">
    <p id="code-message" style="margin: 0; color: #374151; font-size: 0.95rem;">
        Digite o código de 6 dígitos do seu aplicativo autenticador.
    </p>
    <p id="recovery-message" style="display: none; margin: 0; color: #374151; font-size: 0.95rem;">
        Digite um dos seus códigos de recuperação de emergência.
    </p>
</div>

<form method="POST" action="{{ route('two-factor.login') }}" id="twoFactorForm">
    @csrf
    
    <div id="code-input" class="form-group">
        <label for="code" class="form-label">Código de Autenticação</label>
        <input type="text" 
               class="form-control js-input text-center @error('code') is-invalid @enderror" 
               id="code" 
               name="code" 
               inputmode="numeric"
               maxlength="6"
               placeholder="000000"
               style="font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: 600;"
               autofocus
               autocomplete="one-time-code">
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div id="recovery-input" class="form-group" style="display: none;">
        <label for="recovery_code" class="form-label">Código de Recuperação</label>
        <input type="text" 
               class="form-control js-input @error('recovery_code') is-invalid @enderror" 
               id="recovery_code" 
               name="recovery_code"
               placeholder="XXXX-XXXX-XXXX"
               autocomplete="one-time-code">
        @error('recovery_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary js-submit-btn">
        <span>Verificar</span>
    </button>
    
    <div class="auth-footer">
        <a href="#" class="js-toggle-recovery">
            Usar código de recuperação
        </a>
    </div>
</form>

@push('scripts')
<script>
$(document).ready(function() {
    let usingRecovery = false;
    
    // Toggle between code and recovery
    $('.js-toggle-recovery').on('click', function(e) {
        e.preventDefault();
        usingRecovery = !usingRecovery;
        
        if (usingRecovery) {
            $('#code-input').slideUp(300);
            $('#code-message').slideUp(300);
            setTimeout(() => {
                $('#recovery-input').slideDown(300);
                $('#recovery-message').slideDown(300);
                $('#recovery_code').focus();
            }, 300);
            $(this).text('Usar código de autenticação');
        } else {
            $('#recovery-input').slideUp(300);
            $('#recovery-message').slideUp(300);
            setTimeout(() => {
                $('#code-input').slideDown(300);
                $('#code-message').slideDown(300);
                $('#code').focus();
            }, 300);
            $(this).text('Usar código de recuperação');
        }
    });
    
    // Auto-format code input
    $('#code').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 6) {
            $(this).addClass('pulse-success');
            setTimeout(() => $(this).removeClass('pulse-success'), 500);
        }
    });
    
    // Input effects
    $('.js-input').on('focus', function() {
        $(this).parent().addClass('focused');
    }).on('blur', function() {
        $(this).parent().removeClass('focused');
    });
    
    // Button ripple effect
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
    
    // Form submit
    $('#twoFactorForm').on('submit', function() {
        const submitBtn = $('.js-submit-btn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span>Verificando...</span>');
    });
    
    @if($errors->any())
        $('.is-invalid').addClass('shake');
        setTimeout(() => $('.is-invalid').removeClass('shake'), 500);
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
    
    .pulse-success {
        animation: pulse-success 0.5s ease-out;
    }
    
    @keyframes pulse-success {
        0% { 
            border-color: #00c853;
            box-shadow: 0 0 0 0 rgba(0, 200, 83, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(0, 200, 83, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(0, 200, 83, 0);
        }
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

