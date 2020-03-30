export default {
    data() {
        return {
            requiredRule: [v => !!v || "This field is required"],
            passwordRule: [
                v => !!v || "The password is necessary",
                v => v.length > 7 || "Must be at least 8 characterss"
            ],
            passwordConfirmationRule: [
                v => !!v || "This field is required",
                v => v.length > 7 || "Must be at least 8 characters",
                () => this.pwrd === this.pwrdc || "Passwords do not match"
            ],
            emailRules: [
                v => !!v || "The e-mail field is required",
                v => /.+@.+/.test(v) || "E-mail must be valid"
            ],
        }
    },
    filters: {
        toPad(value) {
            return pad(value, 10);
        },
        toNumero(value) {
            let val = (value / 1).toFixed(0).replace(",", ",");
            return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
    }
}