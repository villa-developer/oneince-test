<template>
  <div>
    <v-container>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn color="success" @click.stop="balanceDialog = true">
            <v-icon left>mdi-currency-usd</v-icon> Add money to my account
          </v-btn>
          <v-btn color="info" v-if="localAccount" @click="paymentDialog = true">
            <v-icon left>mdi-plus</v-icon> Add payment
          </v-btn>
        </v-col>
        <v-col cols="12">
          <h5>Balance: <span> ${{ localAccount ? localAccount.balance : '0' }} </span></h5>
        </v-col>
        <v-col cols="12">
          <v-simple-table height="300px">
            <template v-slot:default>
              <thead>
                <tr>
                  <th class="text-left">User</th>
                  <th class="text-left">Amount</th>
                  <th class="text-left">Acctions</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>user</td>
                  <td>$500</td>
                  <td></td>
                </tr>
              </tbody>
            </template>
          </v-simple-table>
        </v-col>
      </v-row>
    </v-container>
    <v-dialog v-model="balanceDialog" persistent max-width="500px" transition="dialog-transition">
      <v-card>
        <v-card-title>
          Balance
        </v-card-title>
        <v-card-text>
          <v-form ref="balanceForm" id="balanceForm" @submit.prevent="createBalance">
            <input type="hidden" value="put" name="_method" v-if="localAccount"/>
            <input type="hidden" name="user_id" :value="user.id">
            <input type="hidden" name="_token" :value="csrf">
            <v-row>
              <v-col cols="12">
                <v-text-field outlined name="balance" label="Balance" v-model="balance" prepend-inner-icon="mdi-currency-usd" :rules="requiredRule"></v-text-field>
              </v-col>
              <v-col cols="12" class="text-right">
                <v-btn text color="error" @click="balanceDialog = false">close</v-btn>
                <v-btn type="submit" color="success">Add payment</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- PAYMENT DIALOG -->
    <v-dialog v-model="paymentDialog" persistent max-width="500px" transition="dialog-transition">
      <v-card>
        <v-card-title>
          New payment
        </v-card-title>
        <v-card-text>
          <v-form ref="paymentForm" id="paymentForm" @submit.prevent="createPayment">
            <input type="hidden" name="user_id" :value="user.id">
            <input type="hidden" name="_token" :value="csrf">
            <v-row>
              <v-col cols="12">
                <v-text-field 
                  outlined 
                  name="amount" 
                  label="Amount" 
                  v-model="amount" 
                  prepend-inner-icon="mdi-currency-usd" 
                  :rules="requiredRule">
                </v-text-field>
              </v-col>
              <v-col cols="12" class="text-right">
                <v-btn text color="error" @click="paymentDialog = false">close</v-btn>
                <v-btn type="submit" color="success">Update balance</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import rules from './mixin/rulesComponent';
import erroHandler from './mixin/axiosMethodsComponent';
export default {
  mixins: [rules, erroHandler],
  props:['user', 'account', 'payments'],
  data(){
    return {
      csrf: document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      localAccount: this.account,
      balanceDialog: false, // Dialog for add balance
      balance: '0',
      /** PAYMENTS */
      paymentDialog: false,
      localPayments: this.payments,
      amount: 0
    }
  },

  methods: {
    createBalance: function() {
      const route = (this.localAccount) ? '/accounts/' + this.localAccount.id : '/accounts';
      const data = $('#balanceForm').serialize();
      axios.post(route, data).then(response => {
        if(response) {
          this.localAccount = response.data
          this.$notify({ group: 'alert', title: 'Yeah!', text: 'Update successful', position: 'bottom-right'});
          this.balanceDialog = false;
          this.balance = 0
          this.$refs.balanceForm.reset();
        }
      }, error => {
        return this.errorHandler(error.response.status, error.response.data);
      })
    },

    createPayment: function() {
      const paymentData = $('#paymentForm').serialize();

      axios.post('/payments', paymentData).then( response => {
        if(response) {
          this.localPayments.push(response.data.payment);
          this.localAccount = response.data.account;
        }
      }, error => {
        this.$notify({ group: 'alert', type: 'error', title: 'Ups!', text: error.response.data.message, position: 'bottom-right'});
      })
    }
  },
}
</script>