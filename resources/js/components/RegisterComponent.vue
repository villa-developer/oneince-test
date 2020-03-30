<template>
  <v-container>
      <v-row>
          <v-col cols="12">
              <v-card>
                  <v-row>
                      <v-col cols="12" md="6" class="register">
                          <div class="filter"></div>
                      </v-col>
                      <v-col cols="12" md="6">
                          <v-form id="registerForm" ref="registerForm" v-model="formValid" method="post" :action="registerRoute" class="pa-4" @submit.prevent="register">
                              <v-alert type="error" :value="errors.length">
                                  <ul>
                                      <li v-for="(error, i) in errors" :key="i">
                                         <span v-for="e in error" :key="e">{{ e }}</span>
                                      </li>
                                  </ul>
                              </v-alert>
                                <input type="hidden" name="_token" :value="csrf">
                                <v-row>
                                    <v-col cols="12">
                                        <v-text-field name="name" label="Name" outlined :rules="requiredRule"></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-text-field name="email" label="E-mail" outlined :rules="requiredRule"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="pwrd" type="password" name="password" label="Password" outlined :rules="passwordRule"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" md="6">
                                        <v-text-field v-model="pwrdc" type="password" name="password_confirmation" label="Confirm password" outlined :rules="passwordConfirmationRule"></v-text-field>
                                    </v-col>
                                    <v-col cols="12">
                                        <v-btn  type="submit" block color="success" x-large >Complete Registration</v-btn>
                                    </v-col>
                                </v-row>
                            </v-form>
                      </v-col>
                  </v-row>
              </v-card>
          </v-col>
      </v-row>
  </v-container>
</template>

<script>
import rules from './mixin/rulesComponent'

export default {
    mixins: [rules],
    props: ['registerRoute', 'errors'],
    data(){
        return {
            formValid: false,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            pwrd: '',
            pwrdc: '',
        }
    },

    methods: {
        register: function() {
            const route = this.registerRoute;

            if(this.$refs.registerForm.validate()) {
                $('#registerForm').submit();
            }
        }
    }
}
</script>