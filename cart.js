document.addEventListener("DOMContentLoaded", () => {
    // Load cart from localStorage
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    // Function to save cart to localStorage
    function saveCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
    }

    // Function to update cart display
    function updateCart() {
        const cartItems = document.getElementById("cart-items");
        const checkoutItems = document.getElementById("checkout-items");
        const cartTotal = document.getElementById("cart-total");
        const checkoutTotal = document.getElementById("checkout-total");

        if (cartItems) {
            cartItems.innerHTML = "";
            cart.forEach((item, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><img src="${item.image}" class="cart-image"></td>
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>$${(item.price * item.quantity).toFixed(2)}</td>
                    <td><a href="#" class="remove-btn" onclick="removeFromCart(${index})">Remove</a></td>
                `;
                cartItems.appendChild(row);
            });
        }

        if (checkoutItems) {
            checkoutItems.innerHTML = "";
            cart.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td><img src="${item.image}" class="cart-image"></td>
                    <td>${item.name}</td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>$${(item.price * item.quantity).toFixed(2)}</td>
                `;
                checkoutItems.appendChild(row);
            });
        }

        const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
        if (cartTotal) cartTotal.textContent = `Total: $${total.toFixed(2)}`;
        if (checkoutTotal) checkoutTotal.textContent = `Total: $${total.toFixed(2)}`;
    }

    // Add to cart function (used in index.php)
    window.addToCart = function(id, name, price, image) {
        const existingItem = cart.find(item => item.id === id);
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({ id, name, price, image, quantity: 1 });
        }
        saveCart();
        updateCart();
        alert(`${name} has been added to your cart!`);
    };

    // Remove from cart function
    window.removeFromCart = function(index) {
        cart.splice(index, 1);
        saveCart();
        updateCart();
    };

    // Initial update
    updateCart();
});