<template>
<v-container>
  <v-row class="mt-5">
      <v-col cols="12">
          <v-card>
            <v-row>
                <v-col cols="12" md="6" class="login">
                    <div class="filter">
                        <h1>Welcome to my <span>test</span></h1>
                    </div>
                </v-col>
                <v-col cols="12" md="6" class="pa-4">
                    <div class="text-center">
                        <img src="/images/user.png" alt="">
                    </div>
                    <v-form v-model="formValid" method="post" :action="loginRoute">
                        <input type="hidden" name="_token" :value="csrf">
                        <v-row>
                            <v-col class="col" cols="12">
                                <v-text-field name="email" label="E-mail" outlined></v-text-field>
                            </v-col>
                            <v-col class="col" cols="12">
                                <v-text-field type="password" name="password" label="Password" outlined></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-btn type="submir" block color="success" x-large rounded >
                                    <v-icon>mdi-arrow-right</v-icon>
                                </v-btn>
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
export default {
    props: ['loginRoute'],
    data(){
        return {
            formValid: false,
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        }
    }
}
</script>