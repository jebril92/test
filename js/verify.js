const inputs = document.querySelectorAll('.verification-code input');

inputs.forEach((input, index) => {
    input.addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();

        if (this.value.length === 1) {
            if (index < inputs.length - 1) {
                inputs[index + 1].focus();
            } else {
                this.blur();
            }
        }
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
            inputs[index - 1].focus();
        }
    });

    input.addEventListener('paste', function(e) {
        e.preventDefault();
        
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        
        const cleanedText = pastedText.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        
        for (let i = 0; i < Math.min(cleanedText.length, inputs.length); i++) {
            inputs[i].value = cleanedText[i];
        }
        
        const lastFilledIndex = Math.min(cleanedText.length, inputs.length) - 1;
        if (lastFilledIndex < inputs.length - 1) {
            inputs[lastFilledIndex + 1].focus();
        } else if (lastFilledIndex === inputs.length - 1) {
            inputs[lastFilledIndex].blur();
        }
    });
});

let timeLeft = 300;
const timerDisplay = document.querySelector('#timer span');

const updateTimer = () => {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;

    timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

    if (timeLeft === 0) {
        clearInterval(timerInterval);
        document.getElementById('resendBtn').disabled = false;
        timerDisplay.parentElement.textContent = 'Le code a expiré. Veuillez en demander un nouveau.';
    } else {
        timeLeft--;
    }
};

let timerInterval = setInterval(updateTimer, 1000);

document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resendBtn');
    const form = document.getElementById('verificationForm');
    const resendTimerSpan = document.createElement('span');
    resendTimerSpan.id = 'resendTimer';
    resendTimerSpan.style.display = 'none';
    resendTimerSpan.style.marginLeft = '5px';
    resendTimerSpan.style.color = '#666';
    resendTimerSpan.style.fontSize = '12px';
    
    resendBtn.parentNode.insertBefore(resendTimerSpan, resendBtn.nextSibling);
    
    let lastResendTime = localStorage.getItem('lastResendTime');
    let cooldownTimeLeft = 0;
    
    if (lastResendTime) {
        const currentTime = Date.now();
        const elapsedTime = (currentTime - parseInt(lastResendTime)) / 1000;
        
        if (elapsedTime < 60) {
            cooldownTimeLeft = Math.ceil(60 - elapsedTime);
            startResendCooldown(cooldownTimeLeft);
        }
    }
    
    if (resendBtn) {
        resendBtn.addEventListener('click', function(e) {
            if (resendBtn.disabled) {
                e.preventDefault();
                return;
            }
            
            e.preventDefault();
            
            localStorage.setItem('lastResendTime', Date.now().toString());
            
            startResendCooldown(60);
            
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'resend';
            hiddenInput.value = 'true';
            
            form.appendChild(hiddenInput);
            
            form.submit();
        });
    }
    
    function startResendCooldown(seconds) {
        resendBtn.disabled = true;
        resendTimerSpan.style.display = 'inline';
        
        let cooldownInterval = setInterval(() => {
            resendTimerSpan.textContent = `(${seconds}s)`;
            
            if (seconds <= 0) {
                clearInterval(cooldownInterval);
                resendBtn.disabled = false;
                resendTimerSpan.style.display = 'none';
            } else {
                seconds--;
            }
        }, 1000);
        
        resendTimerSpan.textContent = `(${seconds}s)`;
    }
});