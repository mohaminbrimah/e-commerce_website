const products = [
    { id: 1, name: "T-shirt", price: 29.99,image:"images/T-shirt image.jpg.jfif" },
    { id: 2, name: "Mug", price: 49.99,image:"images/Mug image.jpg.jfif" },
    { id: 3, name: "Notebook", price: 19.99,image:"images/Notebook image.jpg.jfif" },
    { id: 4, name: "Notebooks", price: 13.99,image:"images/Notebook image.jpg.jfif"},
]; //Array to hold product data
const cart = []; //Array to hold cart items

function renderProducts(){
    const productContainer = document.getElementById("products");
    productContainer.innerHTML = "";
    products.forEach(product => {
         const productCard =`
         <div class="product-card">
            <img src="${product.image}" alt="${product.name}" class="product-image"/>
            <h3>${product.name}</h3>
            <p>$${product.price.toFixed(2)}</p>
            <button onclick="addToCart(${product.id}">Add to Cart</button>
         </div>
         `;
         productContainer.innerHTML += productCard;

    });
}

renderProducts();
