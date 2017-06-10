<style>
    .payment{
        overflow: auto;
        margin-top: 20px;
    }
    .payment .left-panel{
        background: #2b323a;
        overflow: auto;
    }
    .payment .left-panel .option-list li.active a{
        background: #20252b;
    }
    .payment .left-panel .option-list li.active a, .payment .left-panel .option-list a:hover{
        color: #0aa89e;
    }
    .payment .left-panel .option-list a{
        color: white;
        opacity: 1;
        text-transform: inherit;
    }
    .payment .left-panel .option-list a:hover{
        background: #20252b;
    }
    .payment .left-panel .option-list h4{
        display: inline-block;
        width: 85%;
        vertical-align: middle;
        margin: 0;
    }
    .payment .left-panel .custom-left-bloc{
        float: left;
        width: 20%;
    }
    .payment .left-panel .custom-center-bloc{
        float: left;
        width: 10%;
    }
    .payment .left-panel .custom-right-bloc{
         display: inline-block;
    }
    .payment .left-panel ul.wizard{
        list-style: none;
    }
    .payment .left-panel ul.wizard li{
        float: left;
        display: none;
        margin: 0 5px;
    }
    .payment .left-panel ul.wizard li.next{
        float: right;
        display: block;
    }
</style>

<template>
    <div class="payment">

        <div class="col-md-12">

            <div class="card">
                <div class="card-head style-primary">
                    <header><i class="fa fa-cc-stripe"></i> Paiement</header>
                </div>
                <div class="tabs-left left-panel">
                    <ul class="card-head nav nav-tabs option-list" data-toggle="tabs">
                        <li class="active">
                            <a :href.prevent="'#field-1'">
                                <h4>Récapitulatif</h4>
                            </a>
                        </li>
                        <li>
                            <a :href.prevent="'#field-2'">
                                <h4>Prolonger mon contrat</h4>
                            </a>
                        </li>
                        <li>
                            <a :href.prevent="'#field-3'">
                                <h4>Mes factures</h4>
                            </a>
                        </li>
                    </ul>
                    <div class="card-body tab-content style-default-bright">
                        <div class="active tab-pane" id="field-1">
                            <h2 class="text-primary mt0">Récapitulatif</h2>
                            <p class="lead">
                                Voici le détail de votre compte
                            </p>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Compte :</strong></div>
                                <div class="col-md-9">{{auth.first_name}} {{auth.last_name}}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Société :</strong></div>
                                <div class="col-md-9">{{ website.society.name }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Date de création :</strong></div>
                                <div class="col-md-9">{{ auth.registered_at.date | moment('DD-MM-YYYY HH:mm:ss') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>État :</strong></div>
                                <div class="col-md-9">
                                    <i v-show="website.state == -1" class="fa fa-clock-o text-warning" aria-hidden="true"> Période d'éssai</i>
                                    <i v-show="website.state == 1" class="fa fa-check text-success" aria-hidden="true"> Actif</i>
                                    <i v-show="website.state == 0" class="fa fa-times text-danger" aria-hidden="true"> Inactif</i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Date d'expiration :</strong></div>
                                <div class="col-md-9">{{ website.expiration_date.date | moment('DD-MM-YYYY HH:mm:ss') }}</div>
                            </div>
                            <hr class="ruler-xl">
                            <p class="lead">
                                Détail sur les paiements
                            </p>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Nombre de paiments :</strong></div>
                                <div class="col-md-9">{{ detail.count_payments }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Montant du dernier paiement :</strong></div>
                                <div class="col-md-9">
                                    <span v-if="detail.last_payment_amount != null">{{ detail.last_payment_amount }} <i :class="'fa fa-' + detail.currency"></i></span>
                                    <span v-else>Aucun paiement</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 text-primary"><strong>Date du dernier paiement :</strong></div>
                                <div class="col-md-9">
                                    <span v-if="detail.last_payment_date != null">{{ detail.last_payment_date.date | moment('DD-MM-YYYY HH:mm:ss') }}</span>
                                    <span v-else>Aucun paiement</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="field-2">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div id="rootwizard" class="form-wizard form-wizard-horizontal">
                                        <form id="payment-form" class="form-horizontal floating-label form-validation" role="form"
                                              novalidate="novalidate">
                                            <div class="form-wizard-nav">
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-primary"></div>
                                                </div>
                                                <ul class="nav nav-justified">
                                                    <li class="active"><a href="#tab1" data-toggle="tab"><span
                                                            class="step">1</span> <span
                                                            class="title">Choisir votre forfait</span></a></li>
                                                    <li><a href="#tab2" data-toggle="tab"><span class="step">2</span>
                                                        <span class="title">Paiement</span></a></li>
                                                    <li><a href="#tab3" data-toggle="tab"><span class="step">3</span>
                                                        <span class="title">Confirmation</span></a></li>
                                                </ul>
                                            </div><!--end .form-wizard-nav -->
                                            <div class="tab-content clearfix">
                                                <div class="tab-pane active" id="tab1">
                                                    <br/><br/>
                                                    <h3 class="mt0 mb40 center-align">Sélectionner le plan qui correspond à vos besoins.</h3>
                                                    <div class="form-group" v-for="(promo, value) in params.promo">
                                                        <div class="col-sm-3">
                                                            <div class="alert alert-info" role="alert" v-html="promo.promo_label"></div>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div class="radio radio-styled">
                                                                <label>
                                                                    <input type="radio" name="choice" v-model="plan" :value="value" checked="checked">
                                                                    <span v-html="promo.label"></span>
                                                                </label>
                                                            </div>
                                                        </div><!--end .col -->
                                                    </div>
                                                    <hr class="ruler-xxl">
                                                    <div class="form-group">
                                                        <div class="col-sm-3">
                                                            <div class="alert alert-info" role="alert">
                                                                Modulez votre forfait
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-9">
                                                            <div class="radio radio-styled custom-left-bloc">
                                                                <label>
                                                                    <input type="radio" name="choice" v-model="plan" value="custom">
                                                                    <span>Souscription pour </span>
                                                                </label>
                                                            </div>
                                                            <div class="form-group custom-center-bloc">
                                                                <input type="number" class="form-control" v-model="params.default.month" min="1"/>
                                                            </div>
                                                            <p class="ml30 custom-right-bloc">mois = <strong class="fz20">{{customTotal}}€ HT</strong></p>
                                                        </div><!--end .col -->
                                                    </div>
                                                </div><!--end #tab1 -->
                                                <div class="tab-pane" id="tab2">
                                                    <br/><br/>
                                                    <div class="col-sm-9">
                                                        <p class="fz2">Montant : <strong class="pull-right">{{total}}€ HT</strong></p>
                                                        <p class="fz2">Total TTC : <strong class="pull-right">{{totalTtc}}€ TTC</strong></p>
                                                        <div class="form-row">
                                                            <label for="card-element" class="mb20">
                                                                Carte de paiement :
                                                            </label>
                                                            <div id="card-element">
                                                                <!-- a Stripe Element will be inserted here. -->
                                                            </div>
                                                            <!-- Used to display form errors -->
                                                            <div id="card-errors" class="text-danger" role="alert"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <img src="public/img/stripe.png" style="width: 100%;">
                                                    </div>
                                                </div><!--end #tab2 -->
                                                <div class="tab-pane" id="tab3">
                                                    <br/><br/>
                                                    <div v-if="response.status == 'success'" class="alert alert-success" role="alert">
                                                        <strong>Paiement accepté !</strong>
                                                        <p>Vous venez de prolonger votre contrat pour {{ response.data.values.month }} mois.</p>
                                                        <p>Votre forfait est donc valable jusqu'au {{ response.data.new_expiration_date.date | moment('DD MMMM YYYY à HH:mm') }}</p>
                                                    </div>
                                                    <div v-else class="alert alert-danger" role="alert">
                                                        {{ response.message }}
                                                    </div>
                                                </div><!--end #tab3 -->
                                            </div><!--end .tab-content -->
                                            <ul class="wizard p20">
                                                <li class="previous"><a class="btn-raised btn btn-default">Revenir en
                                                    arrière</a></li>
                                                <li class="next"><a class="btn-raised btn btn-primary">Étape suivante</a>
                                                </li>
                                            </ul>
                                        </form>
                                    </div><!--end #rootwizard -->
                                </div><!--end .col -->
                            </div><!--end .row -->
                        </div>
                        <div class="tab-pane" id="field-3">
                            <div class="row">
                                <div class="col-lg-12">
                                    <datatable :config="datatable_config" :reload="reload_payments" :api="api" :callback="callback"></datatable>

                                </div>
                            </div><!--end .row -->
                        </div>
                    </div><!--end .card-body -->
                </div><!--end .card -->
            </div>
        </div><!--end .col -->
    </div>
</template>

<script type="text/babel">

    import {AppVendor} from '@admin/js/app'
    import {AppForm} from '@admin/js/app'
    import {FormWizard} from '@admin/js/theme/formWizard'

    import moment from 'moment'

    import '@admin/libs/wizard/wizard.css'
    import '@admin/libs/jquery-validation/jquery-validate.min'
    import '@admin/libs/wizard/jquery.bootstrap.wizard.min'

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
                website_id: this.$route.params.website_id,
                detail: {
                    count_payments: 0,
                    currency: 'eur',
                    last_payment_date: null,
                    last_payment_amount: null
                },
                params: {
                    default: {
                        month: 1,
                        amount: null
                    }
                },
                plan: 'custom',
                total: 0,
                stripe: null,
                card: null,
                response: {
                    status: 'error',
                    message: 'Aucun paiement effectué',
                    data: {}
                },
                api: payment_api.get_website_payments + this.$route.params.website_id,
                datatable_config: {
                    columns: {
                        'Titre': {"data": "title"},
                        'Référence': {"data": "reference"},
                        'Montant HT': {"data": "amount"},
                        'Montant TTC': {"data": "amount"},
                        'Date': {"data": "created_at"},
                        'Action': {"data": null, "orderable": false, "defaultContent": ""}
                    }
                },
                reload_payments: false
            }
        },
        computed: {
            ...mapGetters(['auth', 'website', 'system']),
            customTotal(){
                return (parseInt(this.params.default.month) * parseFloat(this.params.default.amount)).toFixed(2);
            },
            totalTtc(){
                return (((this.detail.tva / 100) + 1) * this.total).toFixed(2);
            }
        },
        methods: {
            ...mapActions(['create', 'read', 'setWebsiteValue']),
            getDetails(){
                this.read({api: payment_api.get_payment_details + this.website_id}).then((response) => {
                    this.detail = response.data;
                })
            },
            initStripe(key){
                this.stripe = Stripe(key);
                let elements = this.stripe.elements();
                let style = {
                    base: {
                        color: '#32325d',
                        lineHeight: '24px',
                        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                        fontSmoothing: 'antialiased',
                        fontSize: '16px',
                        '::placeholder': {
                            color: '#aab7c4'
                        }
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a'
                    }
                };
                this.card = elements.create('card', {style: style});
                this.card.mount('#card-element');
                this.card.addEventListener('change', function (event) {
                    let displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
            },
            initWizard(){
                let o = this;
                $('#rootwizard').bootstrapWizard({
                    onTabClick: () => {
                        return false;
                    },
                    onTabShow: (tab, navigation, index) => {
                        FormWizard()._handleTabShow(tab, navigation, index, $('#rootwizard'));
                    },
                    onPrevious: (tab, navigation, index) => {
                        switch (index){
                            case 0:
                                $('.payment .left-panel ul.wizard li').hide();
                                $('.payment .left-panel ul.wizard li.next').show();
                                $('.payment .left-panel ul.wizard li.next a').html('Étape suivante');
                                break;
                            case 1:
                                $('.payment .left-panel ul.wizard li.next').show();
                                $('.payment .left-panel ul.wizard li.next a').html('<i class="fa fa-check"></i> Valider');
                                break;
                        }
                    },
                    onNext: (tab, navigation, index) => {
                        let form = $('#rootwizard').find('.form-validation');
                        let valid = form.valid();
                        if(!valid) {
                            form.data('validator').focusInvalid();
                            return false;
                        }
                        switch (index){
                            case 1:
                                $('.payment .left-panel ul.wizard li').show();
                                $('.payment .left-panel ul.wizard li.next a').html('<i class="fa fa-check"></i> Valider');
                                if(o.plan == 'custom') o.total = parseFloat(o.customTotal);
                                else if(o.params.promo[o.plan] !== undefined) o.total = parseFloat(o.params.promo[o.plan].amount);
                                break;
                            case 2:
                                o.createToken();
                                return false;
                                break;
                        }
                    }
                });
                o.read({api: payment_api.get_payment_params}).then((response) => {
                    o.params = response.data;
                    if(o.params.stripe.public_key !== undefined) o.initStripe(o.params.stripe.public_key)
                })
            },
            createToken(){
                let o = this;
                this.stripe.createToken(this.card).then((result) => {
                    if (result.error) {
                        let errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        o.pay(result.token);
                    }
                });
            },
            pay(token){
                if(parseFloat(this.total) > 0){
                    let month = 1;
                    if(this.plan == 'custom') month = this.params.default.month;
                    else if(this.params.promo[this.plan] !== undefined) month = this.params.promo[this.plan].month;
                    this.create({
                        api: payment_api.pay + this.website_id,
                        value: {
                            total: this.total,
                            month,
                            stripeToken: token.id
                        }
                    }).then((response) => {
                        this.response = response.data;
                        if(this.response.status == 'success'){
                            this.setWebsiteValue({key: 'expiration_date', value: this.response.data.new_expiration_date});
                            this.setWebsiteValue({key: 'state', value: 1});
                            this.reload_payments = !this.reload_payments;
                        }
                        $('#rootwizard').bootstrapWizard('last');
                        $('.payment .left-panel ul.wizard li.next').hide();
                    }).then(() =>{
                        this.getDetails();
                    });
                }
            },
            callback(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:eq(2)', nRow).html(aData['amount'] + ' <i class="fa fa-' + this.detail.currency + '"></i>');
                let totalTtc = (((this.detail.tva / 100) + 1) * parseFloat(aData['amount'])).toFixed(2);
                $('td:eq(3)', nRow).html(totalTtc + ' <i class="fa fa-' + this.detail.currency + '"></i>');
                $('td:eq(5)', nRow).html('<a class="btn btn-default" href="' + this.system.domain + '/module/payment/get-invoice/'+ this.website_id + '/' + aData['id'] + '" target="_blank"><i class="fa fa-file-text" aria-hidden="true"></i> Facture</a>');
            }
        },
        created() {
            this.getDetails();
        },
        mounted(){
            AppVendor()._initTabs();
            AppForm().initialize();
            this.initWizard();
        }
    }
</script>