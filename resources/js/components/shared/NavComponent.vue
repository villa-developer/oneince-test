<template>
<div>
    <v-toolbar>
          <v-toolbar-title></v-toolbar-title>
          <img src="/images/logo/one-inc-logo-01.png" class="logo" alt="one-inc">
          <v-spacer></v-spacer>
          <template v-if="!user">
            <v-btn text href="/login">
              <v-icon left>mdi-account-circle</v-icon>
              login
            </v-btn>
            <v-btn text href="/register">
              register
            </v-btn>
          </template>
          <template v-else>
            <v-btn text> 
              <v-icon left>mdi-account-circle</v-icon>{{ user.name }}</v-btn>
            <v-btn text @click="logout()">
              logout
            </v-btn>
          </template>
    </v-toolbar>    
    <form id="logout-form" :action="logoutRoute" method="post">
      <input type="hidden" :value="csrf" name="_token">
    </form>
</div>
</template>

<script>
export default {
  props: ['logoutRoute', 'user'],
    data() {
        return {
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        }
    },
    methods: {
      logout: function(){
        const form = document.getElementById('logout-form');
        form.submit()
      }
    }
}
</script>