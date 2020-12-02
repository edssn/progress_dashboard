define(["local_progress_dashboard/vue",
        "local_progress_dashboard/vuetify",
        "local_progress_dashboard/chartcomponent",
    ],
    function(Vue, Vuetify, Chart) {
        "use strict";
        var wwwroot = M.cfg.wwwroot;

        function init(content) {

            console.log(wwwroot);
            console.log({message: 'entrando a init de graph1'});

            Vue.use(Vuetify);
            Vue.component('chart', Chart);

            console.log({message: 'luego de cargar vue y chart'});

            new Vue({
                delimiters: ["[[", "]]"],
                el: "#graph1",
                vuetify: new Vuetify(),
                data() {
                    return {

                    };
                },
                mounted() {
                    console.log({loader: document.querySelector("#pd-loader")});
                    console.log({graph1: document.querySelector("#graph1")});
                    document.querySelector("#pd-loader").style.display = "none";
                    document.querySelector("#graph1").style.display = "block";
                },
                methods: {
                }
            });

            console.log({message: 'saliendo'});
        }

        return {
            init: init
        };
    });