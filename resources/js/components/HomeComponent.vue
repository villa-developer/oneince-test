<template>
  <div>
    <v-container>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn color="success" @click.stop="balanceDialog = true">
            <v-icon left>mdi-currency-usd</v-icon> Add money to my account
          </v-btn>
          <v-btn color="info" v-if="account" @click="paymentDialog = true">
            <v-icon left>mdi-plus</v-icon> Add payment
          </v-btn>
        </v-col>
        <v-col cols="12">
          <h1>Balance: <span> ${{ account ? account.balance : '0' }} </span></h1>
        </v-col>
        <v-col cols="12">
          <v-data-table :headers="headers" :items="payments">
            <template v-slot:item.actions="{ item }">
              <v-btn color="info" small @click="editPayment(item)">
                <v-icon>mdi-circle-edit-outline</v-icon>
              </v-btn>
              <v-btn color="error" small @click="showDeleteAlert(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
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
            <input type="hidden" value="put" name="_method" v-if="account"/>
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
            <input type="hidden" value="put" name="_method" v-if="activePayment"/>
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
                <v-btn text color="error" @click="closePaymentModal()">close</v-btn>
                <v-btn type="submit" color="info">{{ activePayment ? 'Update payment' : 'Add payment' }}</v-btn>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
    <!-- DELETE CONFIRM DIALOG -->
    <v-dialog v-model="deletePaymentDialog" persistent max-width="500px" transition="dialog-transition">
      <v-card>
        <v-card-title>
          <v-icon>mdi-alert-box</v-icon> Are you sure you want to delete the payment?
        </v-card-title>
        <v-card-actions class="text-right">
          <v-btn color="info" text @click="cancelDelete()">Cancelar</v-btn>
          <v-btn color="error" text @click="deletePayment()">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import rules from './mixin/rulesComponent';
import erroHandler from './mixin/axiosMethodsComponent';
export default {
  mixins: [rules, erroHandler],
  props:['user'],
  data(){
    return {
      csrf: document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      account: this.user.account,
      payments: this.user.payments,
      balanceDialog: false, // Dialog for add balance
      balance: '0',
      /** PAYMENTS */
      paymentDialog: false,
      deletePaymentDialog: false,
      activePayment: null,
      editedIndex: -1,
      amount: 0,
      // TABLE
      headers: [
        {text: 'Created at', value: 'created_at'},
        {text: 'Amount', value: 'amount'},
        {text: 'Actions', value: 'actions'}
      ]
    }
  },

  methods: {
    createBalance: function() {
      const route = (this.account) ? '/accounts/' + this.account.id : '/accounts';
      const data = $('#balanceForm').serialize();
      axios.post(route, data).then(response => {
        if(response) {
          this.account = response.data
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
      const route = this.activePayment ? `/payments/${this.activePayment.id}` : '/payments';

      axios.post(route, paymentData).then( response => {
        if(response) {
          if(this.activePayment) {
            this.payments[this.editedIndex].amount = response.data.payment.amount;
            this.closePaymentModal();
            this.$notify({ group: 'alert', title: 'Yeah!', text: 'Update successful', position: 'bottom-right'});
            
          } else  {
            this.payments.push(response.data.payment);
            this.$notify({ group: 'alert', title: 'Yeah!', text: 'Succesful transaction', position: 'bottom-right'});
          }
          this.account = response.data.account;
          this.closePaymentModal();
        }
      }, error => {
        this.$notify({ group: 'alert', type: 'error', title: 'Ups!', text: error.response.data.message, position: 'bottom-right'});
      })
    },

    editPayment: function(payment){
      this.editedIndex = this.payments.indexOf(payment);
      this.activePayment = payment;
      this.amount = this.activePayment.amount;
      this.paymentDialog = true;
    },

    showDeleteAlert: function(payment){
      this.editedIndex = this.payments.indexOf(payment);
      this.activePayment = payment;
      this.deletePaymentDialog = true;
    },

    deletePayment: function(){
      axios.delete(`/payments/${this.activePayment.id}`).then( response => {
        if(response) {
          this.payments.splice(this.editedIndex, 1);
          this.account = response.data;
          this.cancelDelete();
        this.$notify({ group: 'alert', title: 'Yeah!', text: 'Payment removed', position: 'bottom-right'});

        }
      })
    },

    closePaymentModal: function(){
      this.$refs.paymentForm.reset();
      this.paymentDialog = false;
      this.activePayment = false;
      this.paymentDialog = false;
    },

    cancelDelete: function() {
      this.editedIndex = -1;
      this.activePayment = null;
      this.deletePaymentDialog = false;
    }
  },
}
</script>