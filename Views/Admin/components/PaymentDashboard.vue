<style>

</style>

<template>
    <section class="payment-dashboard style-default-bright">
        <div class="section-header">
            <h2 class="text-primary">Paiements</h2>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <h4>Liste des paiements effectués</h4>
                    <datatable :config="datatable_config" :reload="reload_payments" :api="api" :callback="callback"></datatable>
                </div>
            </div><!--end .row -->
        </div>
    </section>
</template>

<script type="text/babel">

    import {payment_api} from '../api'

    import {mapGetters, mapActions} from 'vuex'

    export default{
        components: {
            Datatable: resolve => {
                require(['@front/components/Helper/Datatable.vue'], resolve)
            }
        },
        data(){
            return {
                api: payment_api.all,
                datatable_config: {
                    columns: {
                        'Société': {"data": "society"},
                        'Titre': {"data": "title"},
                        'Référence': {"data": "reference"},
                        'Montant': {"data": "amount"},
                        'Date du paiement': {"data": "created_at"},
                        'Date d\'expiration': {"data": "expiration_date"},
                        'Action': {"data": null, "orderable": false, "defaultContent": ""}
                    }
                },
                reload_payments: false
            }
        },
        computed: {
            ...mapGetters(['system'])
        },
        methods: {
            ...mapActions(['read']),
            callback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
               $('td:eq(0)', nRow).html('<a href="#/website/' + aData['website_id'] + '">' + aData['society'] + '</a>');
               $('td:eq(3)', nRow).html(aData['amount'] + ' <i class="fa fa-' + aData['currency'] + '"></i>');
               $('td:eq(6)', nRow).html('<a class="btn btn-default" href="' + this.system.domain + '/module/payment/get-invoice/'+ aData['website_id'] + '/' + aData['id'] + '" target="_blank"><i class="fa fa-file-text" aria-hidden="true"></i> Facture</a>');
            }
        },
        mounted(){

        }
    }
</script>