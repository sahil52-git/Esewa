document.addEventListener('DOMContentLoaded', () => {

    const productCards = document.querySelectorAll('[data-product-id]');

    productCards.forEach(card => {

        const getCheckoutUrl = (id) => {
            const path = window.location.pathname;
            const base = path.substring(0, path.lastIndexOf('/'));
            return `${base}/views/checkout.php?product_id=${id}`;
        };

        const goToCheckout = () => {
            const id = card.dataset.productId;
            window.location.href = getCheckoutUrl(id);
        };

        card.addEventListener('click', goToCheckout);

        const buyBtn = card.querySelector('.btn-primary');
        if (buyBtn) {
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                goToCheckout();
            });
        }

        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                goToCheckout();
            }
        });
    });

    const esewaForm = document.getElementById('esewaPaymentForm');
    if (esewaForm) {
        esewaForm.addEventListener('submit', function () {
            const btn = this.querySelector('.esewa-pay-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="spin-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    Redirecting to eSewa...
                `;
            }
        });
    }

    document.querySelectorAll('.product-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 80 * i);
    });

    const successIcon = document.querySelector('.status-icon.success');
    if (successIcon) animateSuccessIcon(successIcon);

    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) link.classList.add('active');
    });
});

function animateSuccessIcon(el) {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes successPulse {
            0%   { box-shadow: 0 0 0 0 rgba(96,214,105,0.5); }
            70%  { box-shadow: 0 0 0 20px rgba(96,214,105,0); }
            100% { box-shadow: 0 0 0 0 rgba(96,214,105,0); }
        }`;
    document.head.appendChild(style);
    el.style.animation = 'successPulse 1.4s ease-out 3';
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => showToast('Copied!')).catch(() => {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showToast('Copied!');
    });
}

function showToast(message) {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const style = document.createElement('style');
    style.textContent = `@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}`;
    document.head.appendChild(style);

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = `
        position:fixed;bottom:2rem;right:2rem;z-index:999;
        background:#60d669;color:#000;font-weight:600;
        padding:0.7rem 1.4rem;border-radius:8px;
        font-family:'DM Sans',sans-serif;font-size:0.88rem;
        box-shadow:0 4px 20px rgba(96,214,105,0.4);
        animation:toastIn 0.3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

const spinStyle = document.createElement('style');
spinStyle.textContent = `.spin-icon{animation:spin 0.7s linear infinite;}@keyframes spin{to{transform:rotate(360deg);}}`;
document.head.appendChild(spinStyle);