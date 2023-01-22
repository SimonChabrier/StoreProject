const menu = {

    init:function(){
        console.log('appInit');
        menu.addAllEventLIsteners();
    },

    addAllEventLIsteners:function(){

        let sidenav = document.getElementById("idSidenav");

        document.getElementById('openBtn').addEventListener('click', () => {
            menu.handleOpenNav(sidenav)
        });

        document.getElementById('closeBtn').addEventListener('click', () => {
            menu.handleCloseNav(sidenav)
        });
    },

    handleOpenNav: function(sidenav) {
        sidenav.classList.add("active");
    },

    handleCloseNav: function(sidenav) {
        sidenav.classList.remove("active");
    },

}


document.addEventListener("DOMContentLoaded", menu.init);