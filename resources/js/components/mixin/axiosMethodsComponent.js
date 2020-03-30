export default {
    data() {
        return {}
    },

    methods: {
        /**
         * METODO PARA MANEJAR ERRORES
         */
        errorHandler: function (code, dataError){
            console.log(code, dataError);
            switch (code) {
                case 422:
                    this.listErrorMesages(dataError)
                    break;
                default:
                    this.notification(dataError.message, 'error: ' + code, 'error')
            }
        },

        /**
         * Metodo para crear una lista desordenada
         * @param {* Lista de errores} dataError 
         */
        listErrorMesages: function(dataError){
            const errors = dataError.errors;
            var htmlError = "<ul>";
            for (let error in errors) {
                htmlError += "<li>" + errors[error] + "</li>";
            }
            htmlError += "</ul>";
            this.$notify({
                group: 'alert',
                title: 'Ups',
                type: 'error',
                text: htmlError,
                position: 'bottom-right',
                html: true
                })

            return 
        },
    }
}