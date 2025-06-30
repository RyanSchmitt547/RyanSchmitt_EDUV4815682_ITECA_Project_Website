document.addEventListener("DOMContentLoaded", () => {
    const loggedIn = localStorage.getItem("loggedIn") === "true";

    const loginBtn = document.getElementById("loginBtn");
    const dashboardBtn = document.getElementById("dashboardBtn");
    const logoutBtn = document.getElementById("logoutBtn");

    if (loggedIn) {
        if (loginBtn) loginBtn.style.display = "none";
        if (dashboardBtn) dashboardBtn.style.display = "inline-block";
        if (logoutBtn) logoutBtn.style.display = "inline-block";
    } else {
        if (loginBtn) loginBtn.style.display = "inline-block";
        if (dashboardBtn) dashboardBtn.style.display = "none";
        if (logoutBtn) logoutBtn.style.display = "none";
    }

    if (document.getElementById("productList")) {
        fetch("get_products.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const productList = document.getElementById("productList");
                    productList.innerHTML = "";

                    if (data.products.length === 0) {
                        productList.innerHTML = "<p>No products available.</p>";
                        return;
                    }

                    data.products.forEach((product) => {
                        const div = document.createElement("div");
                        div.classList.add("product-item");
                        div.innerHTML = `
                            <h3>${product.name}</h3>
                            <p>Price: R ${product.price.toFixed(2)}</p>
                            <p>Description: ${product.description}</p>
                            <a href="payment.html?product_id=${product.product_id}">
                                <button>Buy Now</button>
                            </a>
                        `;
                        productList.appendChild(div);
                    });
                } else {
                    document.getElementById("productList").innerHTML =
                        "<p>Failed to load products.</p>";
                }
            })
            .catch(() => {
                document.getElementById("productList").innerHTML =
                    "<p>Error loading products.</p>";
            });
    }
});
