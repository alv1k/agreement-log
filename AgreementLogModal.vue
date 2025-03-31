<template>
    <div
        id="modal"
        class="new-modal modal modal-bg fade show d-block position-fixed "
        tabindex="-1"
        aria-modal="true"
        role="dialog"
        @click="focused ? null : closeModal()"
    >
        <div class="bg-white ms-auto modal-body modal-body-new position-relative" @click.stop role="document" style="height: fit-content; min-height: 100vh;">
            <button class="rounded-5 bg-light position-absolute border-0 fw-bolder" style="height: 30px; width: 30px; left: auto; right: 700px" @click="closeModal">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8.88909 0.889026L5 4.77811M1.11091 8.6672L5 4.77811M5 4.77811L8.88909 8.6672M5 4.77811L1.11091 0.889026" stroke="black" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="m-0 p-0" :class="$store.state.mobileView ? '' : 'd-flex'">
                <h1 class="p-1 fw-bolder fs-1" :class="$store.state.mobileView ? 'w-100' : 'w-50' ">{{ is_edit ? brouse_view ? 'Просмотр договора' : 'Изменить договор' : 'Добавить договор' }} </h1>
                <div class="d-flex align-items-center p-0" :class="$store.state.mobileView ? 'w-100' : 'w-50' ">
                    <button class="btn btn-light fw-bold border-0 rounded-2 cancel-btn" :class="$store.state.mobileView ? 'w-50' : 'ms-auto' " @click="closeModal">Отмена</button>
                    <button v-if="!brouse_view" class="btn bg-purple fw-bold border-0 rounded-2 add-btn text-white" :class="$store.state.mobileView ? 'w-50' : '' " :disabled="!submitIsDisabled || submit_is_loading" @click="submitRequest">{{ is_edit ? 'Изменить' : 'Добавить' }}</button>
                </div>
            </div>
            <div class="border-top pt-2 ps-2 mt-3">
                <p class="fw-bold">Входящие документы</p>
                <div class="mb-3">
                    <label class="fw-bold my-2">Дата</label>
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="7" r="4" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg> -->
                    <input type="date" class="calendar d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.date">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Юр. лицо *</label>
                    <div class="d-flex position-relative">
                        <select required name="" id="" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" :class="requestData.legal_entity ? '' : 'selected' " v-model="requestData.legal_entity">
                            <option value="" selected disabled hidden class="text-secondary">Введите ФИО</option>
                            <option :value="entity.id" v-for="entity in legal_entity_list">
                                {{ entity.name }}
                            </option>
                        </select>
                        <svg width="24" height="24" class="position-absolute" style="top: 10px; right: 15px;">
                            <use xlink:href="/assets/img/i.svg#chevron-down"></use>
                        </svg>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Контрагент</label> 
                    <div v-if="requestData.contractor" class="d-flex p-2 rounded-2 border border-gray bg-disabled w-100">
                        <div class="ms-1" style="height: 16px; width: 16px;" @click="requestData.contractor = null, requestData.contractor_name = null, contractor_input = null">
                            <svg class="bg-danger rounded-2" viewBox="0 0 10 10" fill="black" stroke="black" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="/assets/img/i.svg#close-small"></use>        
                            </svg>
                        </div>
                        <div class="ms-3 fw-bold">
                            <p class="m-0">
                                ИНН: {{ requestData.contractor }}
                            </p>
                            <p class="m-0">
                                {{ requestData.contractor_name }}
                            </p>
                        </div>
                    </div>
                    <div v-else>
                        <input @focus="focused = true" @blur="focused = false" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" type="text" v-model="contractor_input" placeholder="Введите ИНН организации, нажмите Enter" @keydown.enter="getCompanyByINN(contractor_input)">                        
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Исполнитель договора *</label>
                    <select class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" :class="requestData.implementor ? '' : 'selected' " name="" id="" v-model="requestData.implementor">
                        <option value="" selected disabled hidden>Введите исполнителя договора</option>

                        <option :value="legal_entity_list[requestData.legal_entity - 1]?.id">{{ legal_entity_list[requestData.legal_entity - 1]?.name }}</option>
                        <option value="6">Поставщик/подрядчик</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Номер договора *</label>
                    <input @focus="focused = true" @blur="focused = false" type="text" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.agreement_num" placeholder="Введите номер договора">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Сумма, р.</label>
                    <input @focus="focused = true" @blur="focused = false" type="number" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.agreement_sum" placeholder="Введите сумму">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Дата заключения</label>
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="7" r="4" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg> -->
                    <input type="date" class="calendar d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.conclusion_date">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Срок действия</label>
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="7" r="4" />
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                    </svg> -->
                    <input type="date" class="calendar d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.execution_date">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Тип договора *</label>
                    <div class="d-flex position-relative">
                        <select class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" :class="requestData.agreement_type ? '' : 'selected' " name="" id="" v-model="requestData.agreement_type">
                            <option value="" selected disabled hidden>Выберите один из вариантов</option>
                            <option :value="el.id" v-for="el in agreement_types">{{ el.name }}</option>
                        </select>
                        <svg width="24" height="24" class="position-absolute" style="top: 10px; right: 15px;">
                            <use xlink:href="/assets/img/i.svg#chevron-down"></use>
                        </svg>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Наименование договора</label>
                    <input @focus="focused = true" @blur="focused = false" type="text" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.agreement_title" placeholder="Введите наименование договора">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Номер протокола разногласий</label>
                    <input @focus="focused = true" @blur="focused = false" type="text" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.disagreement_num" placeholder="Введите номер протокола разногласий">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Номер и дата ДС или спецификации</label>
                    <input @focus="focused = true" @blur="focused = false" type="text" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.specification_num" placeholder="Введите номер и датту ДС или спецификации">
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Ответственный сотрудник</label>
                    <input @focus="focused = true" @blur="focused = false" type="text" class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" v-model="requestData.employee_full_name" @input="searchEmployee($event)" placeholder="Введите ФИО">
                    <div class="position-absolute rounded pb-1 w-100" v-if="foundedEmployees">
                        <div class="p-2 form-control w-100" role="button" @click.self="setEmployee(user)" v-for="user in foundedEmployees">
                            {{ user.name + ' ' + user.second_name + ' ' + user.last_name }}
                        </div>
                    </div>
                    <div v-else>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold my-2">Примечание</label>                    
                    <select class="d-block py-2 px-3 date_input rounded-2 border border-light w-100" id="" v-model="requestData.signed">
                        <option value="" selected disabled>Введите комментарий по договору</option>
                        <option value="0">Ждет подпись</option>
                        <option value="1">Подписан</option>
                    </select>
                </div>
                <div class="mb-3" v-if="requestData.files.length != 0">
                    <label class="fw-bold my-2">Загруженные файлы</label>                    
                    <div class="d-flex p-2  border-bottom" v-for="file, index in requestData.files">
                        <div class="ps-3">
                            <svg v-if="file.name.includes('.pdf')" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                               <use xlink:href="/assets/img/i.svg#pdf-icon"></use>
                            </svg>
                            <svg v-else width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <use xlink:href="/assets/img/i.svg#docx-icon"></use>
                            </svg>
                        </div>
                        <div class="ms-3">
                            <p class="m-0 fw-bold">
                                {{ file.name }}
                            </p>
                            <p class="m-0" v-if="file.size">
                                {{ app.formatBytes(file.size) ?? 'не определено' }} 
                            </p>
                        </div>
                        <div class="d-flex text-start ms-auto"> 
                            <!-- <p type="button" class="p-2 m-0 actions me-1" v-if="deleted_files[index]">
                                <span class="align-middle">Удален</span>
                                <svg width="20" height="20" viewBox="0 0 36 36" fill="red" xmlns="http://www.w3.org/2000/svg">
                                    <use xlink:href="/assets/img/i.svg#close-small"></use>
                                </svg>
                            </p> -->
                            <div class="d-flex">                                
                                <a :href="file.path" download type="button" class="p-2 actions m-0 text-black" @click.stop>
                                    <svg width="20" height="20" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="/assets/img/i.svg#download"></use>
                                    </svg>
                                </a>
                                <p type="button" class="p-2 text-danger m-0 actions me-1"  @click.stop="deleteFile(requestData.id, file.name, index)">
                                    <svg width="20" height="20" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <use xlink:href="/assets/img/i.svg#delete"></use>
                                    </svg>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <input id='fileUpload' type="file" class="d-none py-2 px-3 date_input rounded-2 border border-light w-100" @change="onFileChange($event.target.files, 'input')" multiple accept=".doc, .docx, .pdf">
                            
                    <form ref="dropzone" target="dummyframe" class="dropzone rounded-2 border-white border-dashed p-2" id="dropzone" @click="fileUpload.click()" role="button">
                        
                        <div style="font-size: 15px;" class="text-center m-3" >
                            <p class="fw-bold">
                                <svg width="24" height="24" class="icon">
                                    <use xlink:href="/assets/img/i.svg#paperclip"></use>
                                </svg>
                               
                                <span class="text-purple mb-2">
                                    {{ uploadFiles ? 'Выбрать заново' : 'Выберите файл'}}
                                </span>
                                    {{ uploadFiles ? 'или перетащить сюда' : 'или перетащите сюда' }} 
                            </p>
                            <p style="font-size: 13px;" class="text-secondary">Файл размером не более 100 МБ в форматах docx, odt, rtf, pdf, pptx ,odp, xlsx, ods, jpg, jpeg, png, gif, zip, rar</p>
                            
                            <div class="dz-message gap-3 row w-100 d-inline-block" v-if="is_extra_files" @click.stop>
                                <p class="rounded-2 bg-gray">Выбранные файлы</p>
                                <div class="d-flex row row-cols-auto pb-3 justify-content-start" style="width: fit-content;">
                                    <div class="d-flex col rounded-2 text-center gap-2 mt-2 position-relative me-2 uploaded-files bg-gray" v-for="(file, index) in uploadFiles">                                    
                                        <div class="m-auto">
                                            <span>
                                                {{ file.name }}
                                            </span><br />
                                            <span>                                            
                                                 Размер - {{ app.formatBytes(file.size) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <iframe name="dummyframe" id="dummyframe" style="display: none;"></iframe>

                    
                </div>   
                <span>
                    * - обязательные поля
                </span>
            </div>
            
        </div>
    </div>
</template>

<script setup>
module.exports = {
    data() {
        return {
            formData: new FormData(),

            requestData: {
                id: null,
                date: null,
                conclusion_date: null,
                execution_date: null,
                legal_entity: '',
                contractor: '',
                contractor_name: '',
                implementor: '',
                agreement_num: '',
                agreement_sum: '',
                agreement_type: '',
                agreement_title: '',
                disagreement_num: '',
                specification_num: '',
                signed: null,
                employee: '',
                files: []
            },            
            uploadFiles: null,
            foundedEmployees: [],
            is_extra_files: false,
            contractor_input: null,
            agreement_types: [
                {
                    name: 'Договор поставки с отсрочкой платежа',
                    selected: false,
                    id: 1
                },
                {
                    name: 'Договор поставки со спецификацией',
                    selected: false,
                    id: 2
                },
                {
                    name: 'Договор оказания услуг',
                    selected: false,
                    id: 3
                },
                {
                    name: 'Договор с поставщиками',
                    selected: false,
                    id: 4
                },
                {
                    name: 'Договор купли/продажи',
                    selected: false,
                    id: 5
                },
                {
                    name: 'Договор поставки с предоплатой',
                    selected: false,
                    id: 6
                },
            ],
            focused: false,
        }
    },
    components: {},
    props: {
        is_edit: {
            type: Boolean,
            default: false
        },
        selected_fields: {
            type: Object,
            required: true
        },
        submit_is_disabled: {
            type: Boolean,
            default: false
        },
        submit_is_loading: {
            type: Boolean,
            default: false
        },
        brouse_view: {
            type: Boolean,
            default: false
        },
        legal_entity_list: {
            type: Array,
            default: []
        },
    },
    computed: {
        submitIsDisabled() {
            return this.requestData.legal_entity && this.requestData.agreement_num && this.requestData.agreement_type && this.requestData.implementor && this.checkPremission()
        },     
        canEdit() {
            return this.$store.getters.canEdit('agreementLog')
        },
        user() {
            return this.$store.state.user
        },
    },
    watch: {
    },
    methods: {
        getCompanyByINN(target) {
            // 143501315647
            
            this.$store.dispatch('agreementLog/getCompany', target)
                .then(res => {
                    this.requestData.contractor = res[0].data.inn
                    this.requestData.contractor_name = res[0].value                    
                    // this.requestData.contractor = res
                }
            )            
        },
        setEmployee(user) {
            this.foundedEmployees = ''
            this.requestData.employee = user.id
            this.requestData.employee_full_name = user.name + ' ' + user.second_name + ' ' + user.last_name
        },
        searchEmployee(ev) {
            if (!ev.target.value == '') {
                this.$store.dispatch('users/search', ev.target.value)
                    .then((res) => {
                        this.foundedEmployees = res.data
                    })
            } else {
                // this.getUsers()
                this.foundedEmployees = null
                this.requestData.employee = ''
            }
        },
        setSelectedFields() {
            
            this.requestData.id = this.selected_fields.id
            this.requestData.date = this.selected_fields.date
            this.requestData.conclusion_date = this.selected_fields.conclusion_date
            this.requestData.execution_date = this.selected_fields.execution_date
            this.requestData.legal_entity = this.selected_fields.legal_entity
            this.requestData.contractor = this.selected_fields.contractor
            this.requestData.contractor_name = this.selected_fields.contractor_name
            
            this.requestData.implementor = this.selected_fields.implementor

            this.requestData.agreement_num = this.selected_fields.agreement_num
            this.requestData.agreement_sum = this.selected_fields.agreement_sum            
            this.requestData.agreement_type = this.selected_fields.agreement_type

            this.requestData.agreement_title = this.selected_fields.agreement_title
            this.requestData.disagreement_num = this.selected_fields.disagreement_num
            this.requestData.specification_num = this.selected_fields.specification_num
            this.requestData.signed = this.selected_fields.signed
            this.requestData.employee = this.selected_fields.employee
            this.requestData.employee_full_name = this.selected_fields.employee_full_name
            this.requestData.files = this.selected_fields.files
           
        },
        setDefaultFields() {
            this.requestData.date = new Date().toLocaleDateString('en-CA')            
        },
        checkPremission() {
            return this.user.role === 'super_admin' || this.user.role === 'admin' || this.user.role === 'editor' && this.canEdit.statement
        },
        closeModal() {
            this.$emit('close')
        },
        submitRequest() {

            if (this.is_edit) {
                this.formData.append('id', this.requestData.id)
            }
            if (this.canEdit.statement) {
                this.formData.append('editors_id', this.canEdit.editors_id)
            }
            this.formData.append('date', this.requestData.date)
            this.formData.append('conclusion_date', this.requestData.conclusion_date)
            this.formData.append('execution_date', this.requestData.execution_date)
            this.formData.append('legal_entity', this.requestData.legal_entity)
            this.formData.append('contractor', this.requestData.contractor)
            this.formData.append('contractor_name', this.requestData.contractor_name)
            
            this.formData.append('implementor', this.requestData.implementor)
            
            this.formData.append('agreement_num', this.requestData.agreement_num)
            this.formData.append('agreement_sum', this.requestData.agreement_sum)
            this.formData.append('agreement_type', this.requestData.agreement_type)

            this.formData.append('agreement_title', this.requestData.agreement_title)
            this.formData.append('disagreement_num', this.requestData.disagreement_num)
            this.formData.append('specification_num', this.requestData.specification_num)
            this.formData.append('signed', this.requestData.signed)
            this.formData.append('employee', this.requestData.employee)

            if (this.uploadFiles) {
                for (let i = 0; i < this.uploadFiles.length; i++) {
                    this.formData.append('files[]', this.uploadFiles[i])
                }
            }

            this.$emit('submit_request', this.formData)
        },
        deleteFile(entry_id, file_name, index) {            
            if(this.is_edit) {
                this.$store.dispatch('agreementLog/deleteAgreementLogFile', {
                    id: entry_id,
                    name: file_name,
                })
                .then((res) => {
                    if(res.data.success) {
                        app.notify({
                            text: `Файл удален <br><span style="font-size: 12px">` + file_name,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'green'
                        })
                        this.requestData.files.splice(index, 1)
                        this.$forceUpdate();                        
                    } else {
                        app.notify({
                            text: `Файл не удалось удалить <br><span style="font-size: 12px">` + file_name,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'black'
                        })
                    }
                })
            }
        },
        onFileChange(files, source) {
            if(source == 'input') {
                this.uploadFiles = files
            } else if(source == 'form') {
                this.uploadFiles = files
            }
            for(let i = 0; i < this.uploadFiles.length; i++) {
                if(this.uploadFiles.item(i).size > 100000000) {
                    alert('Файл слишком большой')
                    return
                }
            }
            this.is_extra_files = true   
        },
    },
    created() {
        if (this.is_edit) this.setSelectedFields()
        else this.setDefaultFields()
        this.$store.dispatch('agreementLog/get')
        
        var lastTarget = null;
        
        window.addEventListener("dragenter", (e) => {
            // unhide our red overlay
            lastTarget = e.target; // cache the last target here
        });
        
        window.addEventListener("dragleave", (e) => {

            if(e.target === lastTarget || e.target === document)
            {
                // 
            }

        });

        window.addEventListener("dragover", (e) => { 
            e.preventDefault();
        });

        window.addEventListener("drop", (e) => {            
            
            e.preventDefault();
            
            this.onFileChange(e.dataTransfer.files, 'form');
            
            // if drop, we pass object file to dropzone
            // var myDropzone = Dropzone.forElement(".dropzone");
            // myDropzone.handleFiles(e.dataTransfer.files);
        });
    }
    
}
</script>

<style scoped>
* {
    font-size: 15px;  
}

.new-modal { 
    width: 100%; 
    position: absolute;
    z-index: 1200;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    height: 100vh;
}
.accordion {
    overflow: hidden;
    max-height: 0;
    transition: all 0.25s ease-out;
}
.btn:hover {
    border-color: unset !important;
}
.modal-body {
    height: 100vh;
    margin-left: auto;
    vertical-align: middle;
    padding: 1.3rem 18px 0px 18px;
}
.modal-bg {
    background-color: rgba(0, 0, 0, 0.4);
}
.border-light {
    border-color: #F0F0F0 !important;
}
::placeholder {
    color: #A19CA2 !important;
    opacity: 1; /* Firefox */
}
::-ms-input-placeholder { /* Edge 12 -18 */
    color: #A19CA2 !important;
}
input:focus {
    outline: none
}
.text-purple {
    color: #6E328C !important;
}
.btn {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}
.pt-2 {
    padding-top: 1.8rem !important;
}
.border-dashed {
    background-image: 
        repeating-linear-gradient(0deg, #1308171A, #1308171A 10px, transparent 10px, transparent 20px, #1308171A 20px), 
        repeating-linear-gradient(90deg, #1308171A, #1308171A 10px, transparent 10px, transparent 20px, #1308171A 20px), 
        repeating-linear-gradient(180deg, #1308171A, #1308171A 10px, transparent 10px, transparent 20px, #1308171A 20px), 
        repeating-linear-gradient(270deg, #1308171A, #1308171A 10px, transparent 10px, transparent 20px, #1308171A 20px);
    background-size: 1px 100%, 100% 1px, 1px 100% , 100% 1px;
    background-position: 0 0, 0 0, 100% 0, 0 100%;
    background-repeat: no-repeat;
}
.uploaded-files {
    min-width: 130px; 
    min-height: 70px;
}
.bg-gray {
    background-color: #ECEBEC; 
}
.cancel-btn {
    height: 44px; 
    background-color: #13081715;
}
.add-btn {
    height: 44px; 
    margin-left: 12px;
}

@media (min-width: 376px) {       
    .modal-body-new {
        width: 42.8rem;
    }
}
@media (max-width: 375px) {
    .modal-body {
        width: 100vw;
    }    
}
</style>
