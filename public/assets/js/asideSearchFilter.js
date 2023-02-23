console.log('search script loaded');
// domcontentloaded pour que les checkbox soit accessibles dans le dom avant de l'utiliser
document.addEventListener('DOMContentLoaded', function() {

const minPriceInput = document.getElementById("min");
const maxPriceInput = document.getElementById("max");
const searchInput = document.getElementById("text");
const brandCheckboxes = document.querySelectorAll(".brandCheckbox");

// Écoute des événements de saisie dans le formulaire
minPriceInput.addEventListener("input", filterProducts);
maxPriceInput.addEventListener("input", filterProducts);
searchInput.addEventListener("input", filterProducts);
brandCheckboxes.forEach((checkbox) => checkbox.addEventListener("change", filterProducts));

// Gestionnaire d'événements pour mettre à jour la valeur de sortie lorsque la valeur du range est modifiée
minPriceInput.addEventListener('input', () => (minOutput.innerHTML = minPriceInput.value));
maxPriceInput.addEventListener('input', () => (maxOutput.innerHTML = maxPriceInput.value));

// Mise à jour de la valeur de sortie en fonction de la valeur du range
minOutput.innerHTML = minPriceInput.value;
minOutput.style.fontSize = 'smaller';
maxOutput.innerHTML = maxPriceInput.value;
maxOutput.style.fontSize = 'smaller';

// get products from json file
async function fetchProducts() {
    
    const URI = window.location.origin;
    
    try {
        const response = await fetch(`${URI}/json/product.json`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.log(error);
    }
}

// stock products in an array
let products = [];

//TODO à revoir mais ça marche - on résout la promesse de fetchProducts() et on récupère les données de la promesse pour les mettre dans un tableau products
fetchProducts().then((data) => {
    data.forEach((product) => {
        product.sellingPrice = Number(product.sellingPrice);
        product.sellingPrice = Math.trunc(product.sellingPrice);
        products.push(product);
    });
});

// filter products with the search criteria
function filterProducts() {

    // Récupération des valeurs saisies dans le formulaire
    let minPrice = Number(minPriceInput.value);
    let maxPrice = Number(maxPriceInput.value);
    const searchTerm = searchInput.value.toLowerCase();
    const selectedBrands = Array.from(brandCheckboxes).filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);

    // Filtrage des produits en fonction des critères on va toggle true ou false pour chaque critère de recherche et on va utiliser ces valeurs pour évaluer si on utilise ou non chaque critère de recherche dans le filtre
    let searchState = {
        minPrice: false,
        maxPrice: false,
        searchTerm: false,
        brandIsSelected: false
    }
    // on met à jour les valeurs de searchState en fonction des critères de recherche utilisés
    if(minPriceInput.value > 0){
        searchState.minPrice = true;
    }
    if(maxPriceInput.value > 0){
        searchState.maxPrice = true;
    }
    if(searchInput.value != '' && searchInput.value.length >= 3){
        searchState.searchTerm = true;
    }
    if(selectedBrands.length > 0){
        searchState.brandIsSelected = true;
    }

    //console.log(searchState);

    // si aucun critère de recherche n'est utilisé on affiche tous les produits
    if(!searchState.minPrice && !searchState.maxPrice && !searchState.searchTerm && !searchState.brandIsSelected){
        resetDivResults();
        return;
    }
    // si j'ai au moins un critère de recherche alors je filtre les produits par rapport à ce critère
    if(searchState.minPrice || searchState.maxPrice || searchState.searchTerm || searchState.brandIsSelected){
        
        let filteredProducts = products.filter(function(product){
            
            let minPriceFilter = true;
            let maxPriceFilter = true;
            let searchTermFilter = true;
            let selectedBrandsFilter = true;

            let selectedSoloBrand = false;
            //console.log(minPriceFilter, maxPriceFilter, searchTermFilter, selectedBrandsFilter)
            
            // comme min et max ne sont pas null par ddéfaut, on les prend en compte directement pour qu'il n'écrase pas les autres critères de recherche parce qu'il ne sont jamais false
            if(searchState.minPrice){
                minPriceFilter = product.sellingPrice >= minPrice;
            }
            if(searchState.maxPrice){
                maxPriceFilter = product.sellingPrice <= maxPrice;
            }
            if(searchState.searchTerm){
                searchTermFilter = product.name.toLowerCase().includes(searchTerm);
            }
            if(searchState.brandIsSelected){
                selectedBrandsFilter = selectedBrands.includes(product.brand.name);
            }

            return minPriceFilter && maxPriceFilter && searchTermFilter && selectedBrandsFilter ;
        });
    
    // sort the filtered products by product.selligPrice
    filteredProducts.sort(function(a, b){
        return a.sellingPrice - b.sellingPrice;
    });
        createProductCard(filteredProducts);
        countResults(filteredProducts.length);  
    }
}

// make product card with the filtered products
function createProductCard(product){
    let searchResults = document.getElementById('searchResults');
    // on nettoie avant d'afficher les résultats ou de les mettre à jour
    resetDivResults();
    // on affiche les résultats
    for(let i = 0; i < product.length; i++){
        
        let section = document.createElement('section')
        section.classList.add('result-card');
        
        section.innerHTML = `
            <h6>${product[i].name}</h6>
            <img class="last-five-picture" src="https://assets.adidas.com/images/h_840,f_auto,q_auto,fl_lossy,c_fill,g_auto/acc1f836e10a4c1191dbae2801556d8d_9366/Chaussure_Ultraboost_5_DNA_Running_Sportswear_Lifestyle_Blanc_GV8747_01_standard.jpg" alt="">
            <span class="catalog-price"><del>${product[i].catalogPrice} €</del></span>
            <span class="selling-price">${product[i].sellingPrice} €</span>
            <div class="productInfo">
                <span class="categorie">Categorie : ${product[i].category.name}</span>
                <span class="sous-categorie">Sous-categorie : ${product[i].subCategory.name}</span>
                <span class="sous-categorie">Marque : ${product[i].brand.name}</span>
                <span class="type">Type : ${product[i].productType.name}</span>
            </div>
            <div class="productInfoFooter">
                                        <span>-9%</span> 
                <span class="productLink"><a href="/product/${product[i].id}">Détail</a></span>
            </div>
        `;
        searchResults.appendChild(section);
    };
}

// reset div searchResults
function resetDivResults(){
    let searchResults = document.getElementById('searchResults');
    searchResults.innerHTML = '';
}
// Make a message with the number of results
function countResults(count){
    let searchResults = document.getElementById('searchResults');
    let h2 = document.createElement('h2');
    h2.classList.add('resultsNumber');
    count > 1 ? h2.innerHTML = `${count} Résultats pour  la recherche prix minimum : ${min.value} € prix maximum : ${max.value} € ${text.value}` : h2.innerHTML = `${count} Résultat pour  la recherche prix minimum : ${min.value} € prix maximum : ${max.value} € ${text.value}`;
    searchResults.prepend(h2);
}

// reset search results and inputs values
document.getElementById('reset').addEventListener('click', function() {
    let searchResults = document.getElementById('searchResults');
    searchResults.innerHTML = '';
    min.value = 0;
    minOutput.innerHTML = min.value;
    max.value = 0;
    maxOutput.innerHTML = max.value;
    text.value = '';
    document.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => { checkbox.checked = false });
    });
});


