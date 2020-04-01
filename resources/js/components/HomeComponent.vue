<template>
  <div>
    <v-container>
      <v-row>
        <v-col cols="12" class="text-right">
          <v-btn class="ma-2" color="success" @click.stop="balanceDialog = true">
            <v-icon left>mdi-currency-usd</v-icon> Add money to my account
          </v-btn>
          <v-btn class="ma-2" color="info" v-if="account" :disabled="commentDrawer" @click="paymentDialog = true">
            <v-icon left>mdi-plus</v-icon> Add payment
          </v-btn>
        </v-col>
        <v-col cols="12" class="text-right">
          <h1>Balance: <span> ${{ account ? account.balance : '0' }} </span></h1>
        </v-col>
        <v-col cols="12">
          <v-data-table :headers="headers" :items="payments">
            <template v-slot:item.actions="{ item }">
              <v-btn color="info" small @click="editPayment(item)">
                <v-icon>mdi-circle-edit-outline</v-icon>
              </v-btn>
              <v-badge bordered color="error" overlap :content="item.comments.length" v-if="item.comments.length">
                <v-btn color="primary" small @click="showComments(item)">
                  <v-icon>mdi-chat</v-icon>
                </v-btn>
              </v-badge>
              <v-btn color="primary" small @click="showComments(item)" v-else>
                  <v-icon>mdi-chat</v-icon>
              </v-btn>
              <v-btn color="error" small @click="showDeleteAlert(item)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-container>

    <!-- EDIT BALANCE -->
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
                <v-btn type="submit" color="success">Update balance</v-btn>
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
          {{activePayment ? 'Update payment' : 'New payment'}}
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
<!--               <v-col cols="12">
                <v-file-input name="file" label="File" outlined counter show-size></v-file-input>
              </v-col> -->
              <v-col cols="12" v-if="!activePayment">
                <v-textarea outlined name="body" label="Comment" value=""></v-textarea>
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

    <!-- COMMENT MODAL -->
    <v-dialog v-model="addComentDialog" persistent max-width="500px" transition="dialog-transition">
      <v-card>
        <v-card-title>
          New comment
        </v-card-title>
        <v-card-text>
          <v-form ref="addCommentForm" id="addCommentForm" @submit.prevent="addComment()">
            <input type="hidden" :value="csrf" name="_token">
            <input type="hidden" :value="user.id" name="user_id">
            <input type="hidden" :value="activePayment.id" name="payment_id" v-if="activePayment">
            <v-textarea outlined name="body" label="Comment" value="" :rules="requiredRule"></v-textarea>
            <div class="text-right">
              <v-btn text color="error" @click="addComentDialog = false; activePayment = null">close</v-btn>
              <v-btn type="submit" color="primary">Add payment</v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- COMMENTS DRAWER -->
    <v-navigation-drawer v-model="commentDrawer" right absolute>
      <v-app-bar>
        <v-app-bar-nav-icon @click="commentDrawer = false; activePayment = false">
          <v-icon>mdi-close</v-icon>
        </v-app-bar-nav-icon>
        <v-toolbar-title>Comentarios</v-toolbar-title>
      </v-app-bar>

      <v-btn class="ma-3" color="info" small @click="addComentDialog = true">Add comment</v-btn>
      <div class="comments pa-3" v-for="(comment,i) in comments" :key="i">
        <div class="comment">
          {{ comment.body }}
          <div class="text-right">
            <v-btn color="error" text small @click="deletShowDialog(comment, i)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </div>
        </div>
      </div>
    </v-navigation-drawer>

    <!-- DELETE CONFIRM DIALOG -->
    <v-dialog v-model="deleteCommentDialog" persistent max-width="500px" transition="dialog-transition">
      <v-card>
        <v-card-title>
          <v-icon>mdi-alert-box</v-icon> Are you sure you want to delete this comment?
        </v-card-title>
        <v-card-actions class="text-right">
          <v-btn color="info" text @click="deleteComment = null; commentIndex = null; deleteCommentDialog = false">Close</v-btn>
          <v-btn color="error" text @click="commentDelete()">Delete</v-btn>
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
      // COMMENTS
      commentDrawer: false,
      comments: [],
      addComentDialog: false,
      deleteComment: null,
      commentIndex: null,
      deleteCommentDialog: false,
      // TABLE
      headers: [
        {text: 'Date of payment', value: 'created_at'},
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
    },

    // COMMENT
    showComments: function(payment) {
      this.activePayment = payment;
      this.comments = payment.comments;
      this.commentDrawer = true;
    },

    addComment: function (){
      const data = $('#addCommentForm').serialize();
      axios.post('/comments', data).then( response => {
        this.comments.push(response.data);

        this.addComentDialog = false;
        this.$refs.addCommentForm.reset();
      })
    },

    deletShowDialog: function(comment, idx) { 
      this.deleteComment = comment;
      this.commentIndex = idx
      this.deleteCommentDialog = true;
    },

    commentDelete: function(){
      axios.delete(`/comments/${this.deleteComment.id}`).then( response => {
        if(response) {
          console.log(response.data);

          this.comments.splice(this.commentIndex, 1);

          this.deleteComment = null;
          this.commentIndex = null;
          this.deleteCommentDialog = false;
        }
      });
    }
  },
}
</script>