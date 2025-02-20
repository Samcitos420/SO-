function openRegistrationModal() {
    document.getElementById('registrationModal').style.display = 'block';
}

function closeRegistrationModal() {
    document.getElementById('registrationModal').style.display = 'none';
}

function validateForm() {
    const password = document.getElementById('reg_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        alert("Heslá sa neshodujú!");
        return false; // Zastaví odoslanie formulára
    }
    return true; // Formulár môže byť odoslaný
}

// Zatvor modálne okno, ak sa klikne mimo jeho obsahu
window.onclick = function(event) {
    const modal = document.getElementById('registrationModal');
    if (event.target === modal) {
        closeRegistrationModal();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const userIcon = document.getElementById("userIcon");
    const loginForm = document.getElementById("loginForm");
    const userMenu = document.getElementById("userMenu");

    // Načítanie skrytej hodnoty z HTML
    const isLoggedIn = document.getElementById('isLoggedIn').value === 'true';

    if (isLoggedIn) {
        loginForm.classList.add("hidden");
        userMenu.classList.remove("hidden");
    } else {
        loginForm.classList.remove("hidden");
        userMenu.classList.add("hidden");
    }

    userIcon.addEventListener("mouseover", function() {
        if (isLoggedIn) {
            userMenu.classList.toggle("hidden");
        }
    });

    document.addEventListener("click", function(event) {
        if (!userIcon.contains(event.target) && !userMenu.contains(event.target)) {
            userMenu.classList.add("hidden");
        }
    });
});

function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        } else {
            alert('Vyskytla sa chyba pri pridávaní do košíka.');
        }
    });
}

let cartVisible = false;

function showCart() {
    const tooltip = document.querySelector('.cart-tooltip');
    tooltip.style.display = 'block';
    cartVisible = true;
}

function hideCart() {
    if (!cartVisible) {
        const tooltip = document.querySelector('.cart-tooltip');
        tooltip.style.display = 'none';
    }
}

function keepCartVisible() {
    cartVisible = true;
}

// Skryť tooltip, ak sa myš opustí tooltip
document.querySelector('.cart-tooltip').addEventListener('mouseleave', () => {
    cartVisible = false;
    hideCart();
});

