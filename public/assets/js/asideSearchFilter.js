console.log('search script loaded');
// domcontentloaded pour que les checkbox soit accessibles dans le dom avant de l'utiliser
document.addEventListener('DOMContentLoaded', function() {

const minPriceInput = document.getElementById("min");
const maxPriceInput = document.getElementById("max");
const searchInput = document.getElementById("text");
const brandCheckBoxes = document.querySelectorAll("input[name='brand']");
// select all the checkboxes with name category
const categoryCheckBoxes = document.querySelectorAll("input[name='category']");

// Écoute des événements de saisie dans le formulaire
minPriceInput.addEventListener("input", filterProducts);
maxPriceInput.addEventListener("input", filterProducts);
searchInput.addEventListener("input", filterProducts);
brandCheckBoxes.forEach((checkbox) => checkbox.addEventListener("change", filterProducts));
categoryCheckBoxes.forEach((checkbox) => checkbox.addEventListener("change", filterProducts));

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

    // on attend 1 secondes parce que le fichier json est trop rapide à charger 
    // il n'est pas encore mis à jour si il y a eu des modifications d'Entité (ajout image etc) quand on arrive ici
    //* A améliorer
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    try {
        const response = await fetch(`${URI}/json/product.json`);
        const data = await response.json();
        console.log(data);
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


// plus lent que le fetch sur le json 

// async function getApiProducts() {

//     const URI = window.location.origin;
    
//     try {
//         const response = await fetch(`${URI}/api/products`);
//         const data = await response.json();
//         return data;
//     } catch (error) {
//         console.log(error);
//     }
// }


// getApiProducts().then((data) => {
//     data.forEach((product) => {
//         product.sellingPrice = Number(product.sellingPrice);
//         product.sellingPrice = Math.trunc(product.sellingPrice);
//         products.push(product);
//     });
// });


// filter products with the search criteria
function filterProducts() {

    // Récupération des valeurs saisies dans le formulaire
    let minPrice = Number(minPriceInput.value);
    let maxPrice = Number(maxPriceInput.value);
    const searchTerm = searchInput.value.toLowerCase();
    const selectedBrands = Array.from(brandCheckBoxes).filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    let selectedCategories = Array.from(categoryCheckBoxes).filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    
    console.log(selectedCategories);
    // console.log(selectedSubCategories);
    // Filtrage des produits en fonction des critères on va toggle true ou false pour chaque critère de recherche et on va utiliser ces valeurs pour évaluer si on utilise ou non chaque critère de recherche dans le filtre
    let searchState = {
        minPrice: false,
        maxPrice: false,
        searchTerm: false,
        brandIsSelected: false,
        categoryIsSelected: false
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
    if(selectedCategories.length > 0){
        searchState.categoryIsSelected = true;
    }

    //console.log(searchState);

    // si aucun critère de recherche n'est utilisé on affiche tous les produits
    if(!searchState.minPrice && !searchState.maxPrice && !searchState.searchTerm && !searchState.brandIsSelected && !searchState.categoryIsSelected){
        resetDivResults();
        return;
    }
    // si j'ai au moins un critère de recherche alors je filtre les produits par rapport à ce critère
    if(searchState.minPrice || searchState.maxPrice || searchState.searchTerm || searchState.brandIsSelected || searchState.categoryIsSelected){
        
        let filteredProducts = products.filter(function(product){
            
            let minPriceFilter = true;
            let maxPriceFilter = true;
            let searchTermFilter = true;
            let selectedBrandsFilter = true;
            let selectedCategoriesFilter = true;
            
            // comme min et max ne sont pas null par défaut, on les prend en compte directement pour qu'il n'écrase pas les autres critères de recherche parce qu'il ne sont jamais false
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
            if(searchState.categoryIsSelected){
                // on concatene le nom de la catégorie et du sous-catégorie retournée par chaque checkbox et on le compare avec la valeur concatenée de la sous categorie et de la categorie de la sous categorie du produit pour savoir si le produit est dans la catégorie sélectionnée
                // ['Enfant Ville'] donne ['enfantville'] donc pas de double comparaison si on a par ex ['Enfant Ville', 'Femme Ville'] ! Ville ne sera pas évalué deux fois.
                // on reçoit ça de twig ici value="{{ cat.catName }} {{ subCat.subCatName  }}" j'aurais pu coller {{ cat.catName }} et {{ subCat.subCatName  }} mais c'est plus propre comme ça 
                // si l'espace est supprimé dans twig ou augmenté pour donner ce format ['Enfant      Ville'] dans le futur ça ne plantera pas l'espace blanc sera toujours supprimé ci-dessous
                selectedCategories = selectedCategories.map(category => category.replace(/\s/g, '').toLowerCase());
                // ensuite on compare la valeur de chaque checkbox avec la valeur de la sous categorie et de la categorie concaténées
                selectedCategoriesFilter = selectedCategories.includes(product.subCategory.categories[0].name.replace(/\s/g, '').toLowerCase() + product.subCategory.name.replace(/\s/g, '').toLowerCase());
            }

            return minPriceFilter && maxPriceFilter && searchTermFilter && selectedBrandsFilter && selectedCategoriesFilter;
        });
    
    console.log(filteredProducts);

    // sort the filtered products by product.selligPrice
    filteredProducts.sort(function(a, b){
        return a.sellingPrice - b.sellingPrice;
    });
        createProductCard(filteredProducts);
        countResults(filteredProducts.length);  
    }
}

// make product card  template with the filtered products
function createProductCard(product){
    let searchResults = document.getElementById('searchResults');
    // on nettoie avant d'afficher les résultats ou de les mettre à jour
    resetDivResults();
    // on affiche les résultats
    for(let i = 0; i < product.length; i++){
        
        let section = document.createElement('section')
        section.classList.add('result-card');


        // Affiche la dernière image du produit qui est contenu dans le tableau pictures du produit
        // C'est ce qu'on fait dans Twig avec le filtre last()
        // let productMainPicture = '';
        // if (product[i].pictures.length > 0) {
        //     const lastPicture = product[i].pictures[product[i].pictures.length - 1];
        //     console.log(lastPicture);
        //     productMainPicture = `/uploads/files/pictures/${lastPicture.fileName}" alt=${lastPicture.alt} title=${lastPicture.name}`;
        // } else {
        //     productMainPicture = '/assets/pictures/defaultSneakersPicture.webp';
        // }

        // Affiche la première image du produit qui est contenu dans le tableau pictures du produit
        // C'est ce qu'on fait dans Twig avec le filtre first()
        let productMainPicture = '';
        product[i].pictures[0]?.fileName != undefined ? productMainPicture = `/uploads/files/pictures/${product[i].pictures[0].fileName}" alt=${product[i].pictures[0].alt} title=${product[i].pictures[0].name}` : productMainPicture = '/assets/pictures/defaultSneakersPicture.webp';

        // product[i].subCategory.categories[0].name ici je récupère le no de la categorie à la quelle appartient la sous categorie du produit
        section.innerHTML = `
            <h6>${product[i].name}</h6>
            <img class="last-five-picture" src="${productMainPicture}">
            <span class="catalog-price"><del>${product[i].catalogPrice} €</del></span>
            <span class="selling-price">${product[i].sellingPrice} €</span>
            <div class="productInfo">
                <span class="categorie">Categorie : ${product[i].category == null ? product[i].subCategory.categories[0].name : product[i].category.name}</span>
                <span class="sous-categorie">Sous-categorie : ${product[i].subCategory.name == null ? 'Pas de sous categorie' : product[i].subCategory.name}</span>
                <span class="sous-categorie">Marque : ${product[i].brand.name}</span>
                <span class="type">Type : ${product[i].productType.name}</span>
            </div>
            <div class="productInfoFooter">
                <span>-9%</span> 
                <span class="productLink"><a href="/product/${product[i].id}">Détail</a></span>
            </div>
        `;
        searchResults.appendChild(section);

        // lien vers la page produit ajouté au click sur la card produit pour cards produitsretournées par les résultats de recherche
        section.addEventListener('click', function(){
            let href = $(this).find("a").attr("href");
            $(".productLink").attr("href", window.location.origin + href);
            window.open($(".productLink").attr("href"), "_self");
        });
    };

    
}

// reset div searchResults
function resetDivResults(){
    let searchResults = document.getElementById('searchResults');
    searchResults.innerHTML = '';
}

// display the number of results and the selected filters
function countResults(count){
    let searchResults = document.getElementById('searchResults');
    let h6 = document.createElement('h6');
    h6.classList.add('resultsNumber');
  
    let text = {
        min: '',
        max: '',
        search: '',
        brands: '',
        number: '',
        categories: ''
    }
    
    let selectedBrands = Array.from(brandCheckBoxes).filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    let selectedCategories = Array.from(categoryCheckBoxes).filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    console.log(selectedCategories);
    // Ajouter un espace après la virgule pour chaque élément  du tableau des marques sauf le dernier
    text.brands = selectedBrands.reduce((acc, brand, index) => {
      if (index > 0) {
        acc += ' <i class="bi bi-three-dots-vertical" style="margin-left:10px; color:#c7c721; font-size:1.2rem;"></i> ';
      }
      acc += brand;
      return acc;
    }, '').trim();

    text.categories = selectedCategories.reduce((acc, category, index) => {
        if (index > 0) {
            acc += ' <i class="bi bi-three-dots-vertical" style="margin-left:10px; color:#c7c721; font-size:1.2rem;"></i> ';
        }
        acc += category;
        return acc;
    }, '').trim();

    let icon = '<i class="bi bi-caret-right-fill" style="margin-left:10px; color:#c7c721; font-size:1.2rem;"></i>';
  
    text.min = min.value == 0 ? '' : `à partir de ${icon} ${min.value} €${max.value == 0 ? '' : `${icon}` }`;
    text.max = max.value == 0 ? '' : `jusqu'à ${icon} ${max.value} €${searchInput.value == '' ? '' :  `${icon}` }`;
    text.search = searchInput.value.length <= 2 ? '' : `contenant ${icon} ${searchInput.value}${selectedBrands.length == 0 ? '' : ' '}`;
    text.brands = selectedBrands.length == 0 ? '' : `${icon} dans ${selectedBrands.length > 1 ? 'les' : 'la'} marque${selectedBrands.length > 1 ? 's' : ''} ${icon} ${text.brands}`;
    text.categories = selectedCategories.length == 0 ? '' : `${icon} dans ${selectedCategories.length > 1 ? 'les' : 'la'} catégorie${selectedCategories.length > 1 ? 's' : ''} ${icon} ${text.categories}`;

    
    count == 0 ? text.number = 'Aucun' : text.number = count;
    count > 1 ? h6.innerHTML = `${text.number} Résultats pour  la recherche ${text.min} ${text.max} ${text.search} ${text.brands} ${text.categories}` :
                h6.innerHTML = `${text.number} Résultat pour  la recherche ${text.min} ${text.max} ${text.search}  ${text.brands} ${text.categories}`;
  
    searchResults.prepend(h6);
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


