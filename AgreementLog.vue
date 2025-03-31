<template>
    <div class="agreement-log" :class="showModal ? 'overflow-hidden' : ''">
        <div :class="$store.state.mobileView ? 'd-flex flex-column-reverse' : ''" style="z-index: 2000;">
            <app-navbar></app-navbar>
            <app-header></app-header>
        </div>
        <div class="page-wrapper" :class="$store.state.navbar.stack ? 'body-stacked-margin' : ''">
            <div class="page-header">
                <div class="align-items-center">
                    <!-- <div class="col">
                        <h2 class="page-title">{{ $route.meta.title }}</h2>
                    </div> -->
                    <div class="d-flex gap-2 p-0" :class="window.innerWidth <= 834 ? 'flex-column' : 'flex-row-reverse' ">
                        <button 
                            :class="window.innerWidth <= 834 ? 'mb-2 ms-0' : 'ms-auto'" 
                            class="bg-deep-purple text-white rounded-3 add-row" 
                            @click="openModal" 
                            style="width: 165px;"
                        >
                            Добавить запись</button>
                        <app-datepicker></app-datepicker>
                    </div>
                </div>
            </div>
            <div class="page-body">
                <div class="input-icon gap-2 d-flex">
                    <span class="input-icon-addon">
                        <span v-if="loading" class="input-icon-addon-loading">
                            <div class="spinner-border spinner-border-sm text-secondary" role="status"></div>
                        </span>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <use xlink:href="/assets/img/i.svg#search" transform="scale(0.9, 0.9)"/>
                        </svg>
                    </span>
                    <input type="text" name="subject" placeholder="Введите поисковый запрос" autocomplete="off" class="rounded-3 border me-3 search-input" style="min-width: 100px; width: 678px;" v-model="search_target" v-on:keydown.enter="startSearchRequest(), loading = true">
                    <br />
                    <button class="bg-deep-purple text-white rounded-3 border-0 search-btn" type="submit" @click="startSearchRequest()">
                        Искать
                    </button>
                </div>
                <div class="align-items-center gap-2" :class="window.innerWidth <= 834 ? 'pt-3':'d-flex py-3 '">
                    <div :class="[$store.state.mobileView ? 'row m-0 gap-3 mb-3': '', $store.state.tabletView ? 'row row-cols-3 m-0 gap-2 mb-3': ' ', !$store.state.tabletView && !$store.state.mobileView ? 'd-flex gap-2' : '']">
                        <div class="bg-select-chevron d-flex d-inline-block select-dropdown position-relative text-nowrap w-fit-content" :class="[$store.state.mobileView ? 'col': '', $store.state.tabletView ? 'col': '', !$store.state.tabletView && !$store.state.mobileView ? '' : '']" name="" id="" @click="showFilters($event), filter_type = 'entity'">
                            Юр. лицо ({{ selected_entity.length }})
                            <!-- <svg width="24" height="24">
                                <use xlink:href="/assets/img/i.svg#chevron-down"></use>
                            </svg> -->
                        </div>
                        <div class="bg-select-chevron d-flex d-inline-block select-dropdown position-relative text-nowrap w-fit-content" :class="[$store.state.mobileView ? 'col-6': '', $store.state.tabletView ? 'col': '', !$store.state.tabletView && !$store.state.mobileView ? '' : '']" name="" id="" @click="showFilters($event), filter_type = 'type'">
                            Тип договора ({{ selected_types.length }})
                        </div>
                        <div class="bg-select-chevron d-flex d-inline-block select-dropdown position-relative text-nowrap w-fit-content" :class="[$store.state.mobileView ? 'col-9': '', $store.state.tabletView ? 'col': '', !$store.state.tabletView && !$store.state.mobileView ? '' : '']" name="" id="" @click="showFilters($event), filter_type = 'implementor'">
                            Исполнитель договора ({{ selected_implementor.length }})
                        </div>
                        <div class="w-100" v-if="$store.state.tabletView"></div>
                        <label class="form-check d-inline-block mb-0 w-fit-content"  :class="[$store.state.mobileView ? 'col mt-1': '', $store.state.tabletView ? 'col-3': '', !$store.state.tabletView && !$store.state.mobileView ? 'pt-1 ms-2' : '']">
                            <input class="form-check-input mt-1 custom-radio" name="corr_type" type="radio" checked :class="signed == 'all' ? 'bg-purple' : ''" @click="signed = 'all'"  style="width: 20px; height: 20px;"/>
                            <span class="form-check-label fs-4 ms-2 mt-1 fw-bold">Все</span>
                        </label>
                        <label class="form-check mb-0 d-inline-block w-fit-content" :class="[$store.state.mobileView ? 'col-6':' ms-3', $store.state.tabletView ? 'col-3': '', !$store.state.tabletView && !$store.state.mobileView ? 'pt-1' : '']">
                            <input class="form-check-input mt-1 custom-radio" name="corr_type" type="radio" :class="signed == '1' ? 'bg-purple' : ''" @click="signed = '1'"  style="width: 20px; height: 20px;"/>
                            <span class="form-check-label fs-4 ms-2 mt-1 fw-bold">Подписанные</span>
                        </label>
                        <label class="form-check ms-3 mb-0 d-inline-block w-fit-content" :class="[$store.state.mobileView ? 'col': '', $store.state.tabletView ? 'col-3': '', !$store.state.tabletView && !$store.state.mobileView ? 'pt-1' : '']" style="white-space: nowrap;">
                            <input class="form-check-input mt-1 custom-radio" name="corr_type" type="radio" :class="signed == '0' ? 'bg-purple' : ''" @click="signed = '0'"  style="width: 20px; height: 20px;"/>
                            <span class="form-check-label fs-4 ms-2 mt-1 fw-bold">Ждут подписи</span>
                        </label>
                        <!-- <label class="form-check ms-3 mb-0 d-inline-block w-fit-content" :class="[$store.state.mobileView ? 'col': '', $store.state.tabletView ? 'col-3': '', !$store.state.tabletView && !$store.state.mobileView ? 'pt-1' : '']" style="white-space: nowrap;">
                            <input class="form-check-input mt-1 custom-radio" name="corr_type" type="radio" :class="archive == true ? 'bg-purple' : ''" @click="archive = !archive"  style="width: 20px; height: 20px;"/>
                            <span class="form-check-label fs-4 ms-2 mt-1 fw-bold">Удаленные</span>
                        </label> -->
                    </div>               
                </div>
                <div class="card rounded-4">
                    <agreement-log-table
                        :user="user"  
                        :sort="sort"
                        :rows="entriesObject.data" 
                        :legal_entity_list="legal_entity_list"
                        :search_target="search_target"
                        :loading="loading"
                        @sorting="sorting"
                        @edit="editRow" 
                        @update="setAgreementLog()"
                        @brouse="brouse"
                    >
                    </agreement-log-table>
                    <app-pagination
                        :pages="entriesObject.pages"
                        :current-page="page"
                        :limit="limit"
                        :count="entriesObject?.data?.length"
                        @page="setPage"
                        @more="showMore"
                    ></app-pagination>
                </div>
            </div>
            <app-footer></app-footer>
        </div>
        <transition name="fade">
            <agreement-log-modal
                v-if="showModal"
                :is_edit="is_edit"
                :selected_fields="selectedModaldata"
                :legal_entity_list="legal_entity_list"
                :submitIsDisabled="submitIsDisabled"
                :brouse_view="brouseView"
                @close="closeModal"
                @submit_request="submitRequest"
            ></agreement-log-modal>
        </transition>
        
        <transition name="fade">
            <agreement-log-additional-modal
                v-if="showFilterModal"
                :filter_type="filter_type"
                :event_data="event_data"
                :input_types_search="input_types_search"
                :types="entriesObject?.types"
                :legal_entity_list="legal_entity_list"
                :selected_types="selected_types"
                :selected_entity="selected_entity"
                :selected_implementor="selected_implementor"
                @set_agreement_log="setAgreementLog()"
                @close_filer_modal="closeFilterModal"
                @submit_request="submitRequest"
            >
            </agreement-log-additional-modal>
        </transition>
    </div>
</template>

<script>

module.exports = {
    data() {
        return {
            page: 1,
            limit: 12,
            showModal: false,
            showFilterModal: false,
            showModalToggle: false,
            sort: 'date_desc',
            is_edit: false,
            selectedModaldata: {},
            submitIsDisabled: false,
            archive: false,
            isDateClicked: false,
            signed: 'all',
            legal_entity_list: [
                {
                    name: 'ИП Петрова И.Е.',
                    short_name: 'ИЕ',
                    id: '1',
                    selected: false,
                },
                {
                    name: 'ООО ТПК Талер',
                    short_name: 'Талер',
                    id: '2',
                    selected: false,
                },
                {
                    name: 'ООО ЯЭК',
                    short_name: 'ЭК',
                    id: '3',
                    selected: false,
                },
                {
                    name: 'ИП Петров С.А.',
                    short_name: 'СА',
                    id: '4',
                    selected: false,
                },
                {
                    name: 'ООО ФБРС(Я)',
                    short_name: 'ФБ',
                    id: '5',
                    selected: false,
                },
            ],
            selected_search_whom: null,
            selected_types: [],
            selected_entity: [],
            selected_implementor: [],
            search_target: null,
            loading: false,
            event_data: null,
            input_types_search: '',
            input_entity_search: '',
            filter_type: null,
            brouseView: false
        }
    },
    components: {
        AgreementLogTable: httpVueLoader('/views/AgreementLog/AgreementLogTable.vue?v=' + version),
        AgreementLogModal: httpVueLoader('/views/AgreementLog/AgreementLogModal.vue?v=' + version),
        AgreementLogWhomModal: httpVueLoader('/views/AgreementLog/AgreementLogWhomModal.vue?v=' + version),
        AgreementLogAdditionalModal: httpVueLoader('/views/AgreementLog/AgreementLogAdditionalModal.vue?v=' + version),
    },
    props: {},
    computed: {
        date() {
            return this.$store.state.date
        },
        entriesObject() {
            return this.$store.getters['agreementLog/entriesObject']
        },
        user() {
            return this.$store.state.user
        },
        users() {
            return this.$store.state.users
        },
        canEdit() {
            return this.$store.getters.canEdit('agreementLog')
        },
    },
    watch: {
        date: {
            handler() {
                this.isDateClicked = true
                this.setAgreementLog('reset')
            },
            deep: true
        },
        limit() {
            this.setAgreementLog()
        },
        archive() {
            this.setAgreementLog()
        },
        signed() {
            this.setAgreementLog()
        },
        selected_search_whom() {
            this.setAgreementLog()
        },
        
    },
    methods: {
        brouse(payload) {
            this.selectedModaldata = payload
            this.is_edit = true
            this.brouseView = true
            this.showModal = true
        },
        startSearchRequest(ev) {
            this.loading = true
            this.setAgreementLog();
        },
        showMore() {
            this.limit += 8
        },
        allTime() {
            this.isDateClicked = false
            this.setAgreementLog('reset')
        },
        editRow(payload) {
            this.selectedModaldata = payload
            this.is_edit = true
            this.brouseView = false
            this.showModal = true
        },
        setAgreementLog(value) {
            if (value === 'reset') this.page = 1

            this.loading = true
            this.$store
                .dispatch('agreementLog/getAgreementLog', {
                    page: this.page,
                    limit: this.limit,
                    from: this.isDateClicked ? this.date.from : null,
                    to: this.isDateClicked ? this.date.to : null,
                    archive: this.archive,
                    sort: this.sort,
                    signed: this.signed,
                    // subject: value ? value : null,
                    // text: value ? value : null,
                    search_target: this.search_target ?? null,
                    search_by_type: this.selected_types.length == 0 ? null : this.selected_types,
                    search_by_entity: this.selected_entity.length == 0 ? null : this.selected_entity,
                    search_by_implementor: this.selected_implementor.length == 0 ? null : this.selected_implementor
                })
                .then((res) => {
                    this.loading = false
                    if (!res?.data.success) {
                        return
                    } else {
                        // if (!this.isDateClicked) {                            
                        //     this.$store.dispatch('setDate', { 
                        //         from: res.data.payload.from, 
                        //         to: res.data.payload.to 
                        //     })
                        // }
                        // this.$router.push({
                        //     query: {
                        //         from: res.data.payload.from, 
                        //         to: res.data.payload.to 
                        //     }
                        // }).catch(() => {})
                    }
                })
        },
        submitRequest(payload) {
            if (this.is_edit) {
                this.$store.dispatch('agreementLog/updateAgreementLogEntry', payload).then((res) => {
                    if (res.data.success) {
                        console.log(res.data.payload);
                        
                        app.notify({
                            text: `<div class="d-flex gap-2"><div>Запись успешно обновлена<br><span style="font-size: 12px"> Номер договора: ` + (res.data.payload.agreement_num != '' ?  res.data.payload.agreement_num  + `</span></div><div><svg width="26" height="26" class="mt-2 ms-3"><use xlink:href="/assets/img/i.svg#square-rounded-check"></use></svg></div></div>` : '' ),
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'green'
                        })
                        this.closeModal()
                        this.setAgreementLog()
                    }
                })
            } else {
                this.$store.dispatch('agreementLog/createAgreementLogEntry', payload).then((res) => {
                    if (res.data.success) {
                        console.log(res.data.payload);
                        app.notify({
                            text: `<div class="d-flex gap-2"><div>Запись успешно добавлена<br><span style="font-size: 12px"> Номер договора: ` + (res.data.payload.agreement_num != '' ?  res.data.payload.agreement_num + `</span></div><div><svg width="26" height="26" class="mt-2 ms-3"><use xlink:href="/assets/img/i.svg#square-rounded-check"></use></svg></div></div>` : '' ),
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'green'
                        })
                        this.closeModal()
                        this.setAgreementLog()
                    }
                })
            }
        },
        
        closeModal() {
            this.is_edit = false
            this.showModal = false
        },
        showFilters(ev, element) {
            this.showFilterModal = true;
            this.event_data = ev.target;
            return true
        },
        closeFilterModal() {
            this.showFilterModal = false
        },
        // closeModalSearchWho() {
        //     this.showModalToggle = false
        // },
        deleteRow(id) {
            return null
        },
        setPage(page) {
            this.page = page
            this.setAgreementLog()
        },
        sorting(value) {
            if (value == 'date_desc' && this.sort == 'date_desc') {
                value = 'date_asc'
            } else if (value == 'date_asc' && this.sort == 'date_asc') {
                value = 'date_desc'
            }
            if (value == 'agreement_sum_asc' && this.sort == 'agreement_sum_asc') {
                value = 'agreement_sum_desc'
            } else if (value == 'agreement_sum_desc' && this.sort == 'agreement_sum_desc') {
                value = 'agreement_sum_asc'
            }
            if (value == 'conclusion_date_desc' && this.sort == 'conclusion_date_desc') {
                value = 'conclusion_date_asc'
            } else if (value == 'conclusion_date_asc' && this.sort == 'conclusion_date_asc') {
                value = 'conclusion_date_desc'
            }
            if (value == 'execution_date_desc' && this.sort == 'execution_date_desc') {
                value = 'execution_date_asc'
            } else if (value == 'execution_date_asc' && this.sort == 'execution_date_asc') {
                value = 'execution_date_desc'
            }
            if (value == 'specification_num_desc' && this.sort == 'specification_num_desc') {
                value = 'specification_num_asc'
            } else if (value == 'specification_num_asc' && this.sort == 'specification_num_asc') {
                value = 'specification_num_desc'
            }
            
            this.sort = value
            this.setAgreementLog('reset')
        },
        openModal() {
            if(this.user.role != 'user' && this.user.role != 'observer' && this.canEdit.statement) {
                this.showModal = true  
            } else {
                app.notify({
                    text: 'У вас недостаточно прав для добавления нового договора.',
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: 'red'
                })
            }
        }
    },
    created() {
        this.loader = true
        this.setAgreementLog()
        
        //const date = new Date()
        //const from = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`
        //this.$store.commit('setDate', { from: from, to: this.date.to })
    }
}
</script>
<style scoped>
    
    .add-row {
        display: flex;
        height: var(--max-height-default-button, 2.75rem);
        padding: 0rem var(--padding-spacing-x4, 1rem);
        justify-content: center;
        align-items: center;
        gap: var(--margin-spacing-x, 0.25rem);
    }
    .form-switch .form-check-input {
        background-size: 1.3rem !important;
    }
    .form-check-input:focus {
        border-color: #90b5e2;
        outline: 0;
        box-shadow: 0 0 0 .25rem rgba(32,107,196,.25)
    }

    .form-check-input:checked {
        background-color: #8446B3;
        border-color: rgba(98,105,118,.24)
    }
    input {
        outline: none;
    }
    .input-icon-addon-loading {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        color: #626976;
        pointer-events: none;
        font-size: 1.2em;
    }
    .page-body {
        margin-bottom: 0px !important;
    }
    .select-dropdown {
        border-radius: var(--radius-radius-x2, 0.5rem);
        border: 1px solid var(--border-primary, rgba(19, 8, 23, 0.10));
        background: var(--bg-surface-primary, #FFF);
        padding: 6px 24px 6px 12px;
    }
    .modal-bg {
        background-color: rgba(0, 0, 0, 0.4);
    }
    .hidden-with-dots {
        overflow:hidden;
        display:inline-block;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .border-light {
        border-color: #1308171A;
    }
    .rounded-bottom-side {
        border-radius: 0 0 12px 12px;
    }
    .btn-purple {
        color: white;
        background-color: #8446B3;
    }
    .form-check-input[type=radio] {
        border-radius: 5px !important;
    }
</style>