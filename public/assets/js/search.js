document.addEventListener("DOMContentLoaded", function() {
    
    const form = document.querySelector("#search-form");
    
    const minError = document.querySelector(".min-error")
    const maxError = document.querySelector(".max-error")
    const searchError = document.querySelector(".text-error")
    
    const min = document.querySelector("#search_min").value;
    const max = document.querySelector("#search_max").value;
    const searchText = document.querySelector("#search_search").value;

    parseInt(min);
    parseInt(max);


    form.addEventListener("submit", function(event) {
        event.preventDefault();
        const formData = new FormData(form);

        if (min === "" && max === "") {

            if(minError.style.display === "none" && maxError.style.display === "none"){
                minError.style.display = "block";
                maxError.style.display = "block";
                minError.innerText = "Veuillez remplir au moins un champ";
                maxError.innerText = "Veuillez remplir au moins un champ";
                setTimeout(function(){
                    minError.style.display = "none";
                    maxError.style.display = "none";
                }, 3000);
            } 
        }

        if (min > max) {
            if(minError.style.display === "none" && maxError.style.display === "none"){
                minError.style.display = "block";
                minError.innerText = "Minimum doit être plus petit que maximum";
                setTimeout(function(){
                    minError.style.display = "none";
                    maxError.style.display = "none";
                }, 3000);
            }
        }

        if (min < 0 || max < 0) {

            if(minError.style.display === "none" && maxError.style.display === "none"){
                minError.style.display = "block";
                maxError.style.display = "block";
                minError.innerText = "Les valeurs ne peuvent pas être négatives";
                maxError.innerText = "Les valeurs ne peuvent pas être négatives";
                setTimeout(function(){
                    minError.style.display = "none";
                    maxError.style.display = "none";
                }, 3000);

            } 
        }

        if (min === NaN || max === NaN) {

            if(minError.style.display === "none" && maxError.style.display === "none"){
                minError.style.display = "block";
                maxError.style.display = "block";
                minError.innerText = "Il faut entrer des nombres";
                maxError.innerText = "Il faut entrer des nombres";

            } setTimeout(function(){
                minError.style.display = "none";
                maxError.style.display = "none";
            }, 3000);
        }

        const data = new URLSearchParams(formData).toString();

        fetch("/search/submit", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: data
        })
        .then(response => response.text())
        .then(data => document.querySelector("#search-results").innerHTML = data)
        .catch(error => console.error(error));
    });

    minError.style.display = "none";
    maxError.style.display = "none";
    searchError.style.display = "none";
});

