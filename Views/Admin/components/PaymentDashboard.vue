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
                params: {
                    stripe: {
                        tva: 20
                    }
                },
                datatable_config: {
                    dom: 'Blfrtip',
                    columns: {
                        'Client': {"data": "client"},
                        'Société': {"data": "society"},
                        'Titre': {"data": "title"},
                        'Référence': {"data": "reference"},
                        'Montant HT': {"data": "amount"},
                        'Total TTC': {"data": "amount"},
                        'Date du paiement': {"data": "created_at"},
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
                $('td:eq(0)', nRow).html('<a href="#/user/' + aData['account_id'] + '">' + aData['client'] + '</a>');
                if(aData['website_id'] == null){
                    $('td:eq(1)', nRow).html('');
                }else{
                    $('td:eq(1)', nRow).html('<a href="#/website/' + aData['website_id'] + '">' + aData['society'] + '</a>');
                }
                $('td:eq(4)', nRow).html(aData['amount'] + ' <i class="fa fa-' + aData['currency'] + '"></i>');
                let totalTtc = (((this.params.stripe.tva / 100) + 1) * parseFloat(aData['amount'])).toFixed(2);
                $('td:eq(5)', nRow).html(totalTtc + ' <i class="fa fa-' + aData['currency'] + '"></i>');
                $('td:eq(7)', nRow).html('<a class="btn btn-default" href="' + this.system.domain + '/module/payment/get-invoice/'+ aData['id'] + '" target="_blank"><i class="fa fa-file-text" aria-hidden="true"></i> Facture</a>');
            }
        },
        created(){
            this.read({api: payment_api.get_payment_params}).then((response) => {
                this.params = response.data;
            })
        }
    }
</script>