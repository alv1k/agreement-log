<template>
    <div class="overflow-auto scrollbar-hide " style="min-height: 221px;">
        <div class="grid-row fw-bold text-secondary">
            <div class="thead-cell gap-2" role="button" @click="sorting('date_asc')">
                <span>
                    Дата внесения
                </span>
                <!-- <span v-text="sort == 'date_desc' ? '▼' : sort == 'date_asc' ? '▲' : ''"></span> -->
                <svg width="24" height="24" class="icon" style="color: #cbcbcb" @click="sorting('date_asc')" role="button" :style="sort.includes('date_desc') ? 'color: #000' : ''">
                    <use :xlink:href="`/assets/img/i.svg#arrow-${sort === 'date_desc' ? 'down' : 'up'}`" />
                </svg>
            </div>
            <div class="thead-cell">
                Юр. лицо
            </div>
            <div class="thead-cell">
                Контрагент
            </div>
            <div class="thead-cell">
                Исполнитель
            </div>
            <div class="thead-cell">
                <!-- <span class="line-clamp-single"> -->
                    Номер договора
                <!-- </span> -->
            </div>
            <div class="thead-cell" role="button" @click="sorting('agreement_sum_asc')">
                Сумма
                <svg width="24" height="24" class="" style="color: #cbcbcb" @click="sorting('agreement_sum_asc')" role="button" :style="sort.includes('agreement_sum') ? 'color: #000' : ''">
                    <use :xlink:href="`/assets/img/i.svg#arrow-${sort === 'agreement_sum_asc' ? 'down' : 'up'}`" />
                </svg>
            </div>
            <div class="thead-cell gap-0" role="button" @click="sorting('conclusion_date_asc')"> 
                Дата заключения
                <!-- <span v-text="sort == 'conclusion_date_desc' ? '▼' : sort == 'conclusion_date_asc' ? '▲' : ''"></span> -->
                <svg width="24" height="24" class="" style="color: #cbcbcb" @click="sorting('conclusion_date_asc')" role="button" :style="sort.includes('conclusion_date') ? 'color: #000' : ''">
                    <use :xlink:href="`/assets/img/i.svg#arrow-${sort === 'conclusion_date_desc' ? 'down' : 'up'}`" />
                </svg>
            </div>
            <div class="thead-cell gap-0" role="button" @click="sorting('execution_date_asc')">
                Дата исполнения
                <!-- <span v-text="sort == 'execution_date_desc' ? '▼' : sort == 'execution_date_asc' ? '▲' : ''"></span> -->
                <svg width="24" height="24" class="" style="color: #cbcbcb" @click="sorting('execution_date_asc')" role="button" :style="sort.includes('execution_date') ? 'color: #000' : ''">
                    <use :xlink:href="`/assets/img/i.svg#arrow-${sort === 'execution_date_desc' ? 'down' : 'up'}`" />
                </svg>
            </div>
            <div class="thead-cell">
                <!-- <span class="line-clamp-single"> -->
                    Тип договора
                <!-- </span> -->
            </div>
            <div class="thead-cell">
                <!-- <span class="line-clamp-single"> -->
                    Наименование договора
                <!-- </span> -->
            </div>
            <div class="thead-cell">
                <!-- <span class="line-clamp-single"> -->
                    № Утвержденного протокола разногласий
                <!-- </span> -->
            </div>
            <div class="thead-cell gap-0" role="button" @click="sorting('specification_num_asc')">
                <!-- <span class="line-clamp-single"> -->
                    Номер и дата ДС или спецификации
                <!-- </span> -->
                <!-- <span v-text="sort == 'specification_num_desc' ? '▼' : sort == 'specification_num_asc' ? '▲' : ''"></span> -->
                <svg width="24" height="24" class="" style="color: #cbcbcb" @click="sorting('specification_num_asc')" role="button" :style="sort.includes('specification_num') ? 'color: #000' : ''">
                    <use :xlink:href="`/assets/img/i.svg#arrow-${sort === 'specification_num_desc' ? 'down' : 'up'}`" />
                </svg>
            </div>
            <div class="thead-cell">
                <span class="line-clamp-single">
                    Ответственный сотрудник
                </span>
            </div>
            <div class="thead-cell">
                Примечание
            </div>
            <div class="thead-cell">
                <svg width="24" height="24" class="">
                    <use xlink:href="/assets/img/i.svg#files"></use>
                </svg>
            </div>
            <div class="thead-cell" v-if="user.role != 'user' && user.role != 'observer' && canEdit.statement">
                
            </div>
        </div>
        <div class="grid-row"  v-for="(row, index) in rows" :key="index" v-if="rows?.length" @click="brouseRow(row)" role="button">
            <div class="cell">
                <span class="line-clamp">
                    {{ convertDate(row.date) }}
                </span>
            </div>
            <div class=" cell ">
                <span class="line-clamp">
                    {{ getLegalEntityName(row.legal_entity) }}   
                </span>                 
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.contractor_name }}   
                </span>                 
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ getLegalEntityName(row.implementor) }}  
                </span>
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.agreement_num }}
                </span>
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.agreement_sum != '0.00' ? num_formatter(row.agreement_sum) + ' ₽' : '-' }} 
                </span>
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ convertDate(row.conclusion_date) }}
                </span>
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ convertDate(row.execution_date) }}
                </span>
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.agreement_type_name}}  
                </span>                    
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.agreement_title }}    
                </span>                
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.disagreement_num }}    
                </span>                
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.specification_num }}    
                </span>                
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ row.employee_full_name }}    
                </span>                                   
            </div>
            <div class="cell">
                <span class="line-clamp">
                    {{ signedText(row.signed) }}     
                </span>                                  
            </div>
            <div class="cell">
                <div class="d-flex" style="color: #E8E7E8 !important;" v-for="file in row.files" role="button">
                    <!-- <svg width="40" height="40" >
                        <use v-if="row.files.path.includes('.pdf')"  href="/assets/img/i.svg#pdf-icon" />
                        <use  v-else xlink:href="/assets/img/i.svg#docx-icon" />
                    </svg> -->
                    <a :href="file.path" download type="button" class="rounded-3 m-0">
                        <svg class="actions rounded-2" v-if="file.path.includes('pdf')" width="24" height="24" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="/assets/img/i.svg#pdf-icon"></use>
                        </svg>
                        <svg class="actions rounded-2" v-if="file.path.includes('docx')" width="24" height="24" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <use xlink:href="/assets/img/i.svg#docx-icon"></use>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="dropdown position-relative" @click.stop="openOptions(index + 1, row)">
                <span role="button" class="btn border-0 actions" data-bs-toggle="dropdown">
                    <svg width="24" height="24" class="my-3">
                        <use xlink:href="/assets/img/i.svg#dots-vertical"/>
                    </svg>
                </span>
                <div class="dropdown-menu options-box rounded-3 position-absolute" style="z-index: 2;">
                    <span class="options-btn dropdown-item" role="button">                        
                        <p type="button" class="p-1 m-0" @click.stop="brouseRow(row)" v-if="row.removed === '0'">
                            <!-- <svg width="24" height="24" class="icon">
                                <use xlink:href="/assets/img/i.svg#edit"></use>
                            </svg> -->
                            Просмотреть
                        </p>
                    </span>
                    <span class="options-btn dropdown-item" role="button" v-if="user.role != 'user' && user.role != 'observer' && canEdit.statement">                        
                        <p type="button" class="p-1 m-0" @click.stop="editRow(row)" v-if="row.removed === '0'">
                            <!-- <svg width="24" height="24" class="icon">
                                <use xlink:href="/assets/img/i.svg#edit"></use>
                            </svg> -->
                            Редактировать
                        </p>
                    </span>
                    <span class="options-btn dropdown-item" role="button" v-if="user.role != 'user' && user.role != 'observer' && canEdit.statement">
                        <p type="button" class="p-1 text-danger m-0" @click.stop="archiveRow(row.id)" v-if="row.removed === '0'">
                            <!-- <svg width="24" height="24" class="icon">
                                <use xlink:href="/assets/img/i.svg#trash"></use>
                            </svg> -->
                            Удалить
                        </p>
                    </span>
                    <!-- <span class="options-btn dropdown-item" role="button" v-if="user.role != 'user' && user.role != 'observer' && canEdit.statement">
                        <p type="button" class="p-1 text-danger m-0" @click.stop="unarchiveRow(row.id)" v-if="row.removed === '1'">
                            Восстановить
                        </p>
                    </span> -->
                </div>
            </div>
            <!-- <div class="options-btn position-relative text-center" v-if="user.role != 'user' && user.role != 'observer'" @click.stop="openOptions(index + 1, row)">
                
                <div class="position-absolute bg-white rounded-4 text-start options-box" v-if="showOptions[index]">
                    <div class="d-block">
                    </div>
                </div>
            </div> -->
        </div>
        
        <div class="p-3" v-if="rows.length == 0 && !loading">
            результаты не найдены
        </div>
    </div>
</template>

<script>
module.exports = {
    data() {
        return {
            
            monthNames: ["января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря"],
            showOptions: [],
        }
    },
    components: {},
    props: {
        rows: {
            type: Array,
            default: []
        },
        sort: {
            type: String
        },
        user: {
            type: String
        },
        loading: {
            type: Boolean,
            default: false
        },
        legal_entity_list: {
            type: Array,
            default: []
        },
        search_target: {
            type: String
        }
    },
    watch: {
        rows() {
            this.rows.forEach(el => {
                this.showOptions.push(false)
            })
        },
        search_target() {
            // для подсветки найденных через поиск слов
            let body = document.body.querySelectorAll("div");
            console.log('body', body);

            let matches = body.matches(this.search_target)
            console.log('matches', matches);
            body.forEach(el => {
                console.log('hmm');                
            })
        }
    },
    computed: {
        submitIsDisabled() {
            return false
        },
        canEdit() {
            return this.$store.getters.canEdit('agreementLog')
        },
    },
    methods: {
        getLegalEntityName(target) {
            if(target == '6'){
                return "Поставщик/подрядчик"
            } else if(target == ''){                 
                return 'Не указано'
            } else {
                let legal_entity_list = this.legal_entity_list;
                let legal_entity_name = null;
                for(let i = 0; i < legal_entity_list.length; i++) {
                    if(legal_entity_list[i].id == target) {
                        legal_entity_name = legal_entity_list[i].name
                        return legal_entity_name
                    }                
                }
            }

        },
        signedText(signed) {
            if(signed == '1'){
                return 'Подписан'
            } else if(signed == '0'){
                return 'Ждет подписания'
            }
        },
        convertDate(date) {
            if(date == '0000-00-00' || date == '1970-01-01') {
                return '-'
            }
            let toConvert = new Date(date)
            let getDate = toConvert.getDate();
            let getMonth = toConvert.getMonth() + 1;
            let getYear = toConvert.getFullYear();

            let monthString = getMonth.toString()
            if(monthString.length == 1) {
                getMonth = '0' + monthString
            }

            return getDate + '.' + getMonth + '.' + getYear
        },
        openOptions(index, row) {
            for(let i = 0; i < this.showOptions.length; i++) {
                this.showOptions[i] = false
            }
            if(index) {
                this.showOptions[index-1] = true
            } 
        },
        getMonth(date) {
            const month = new Date(date).getMonth();
            return this.monthNames[month]
        },
        num_formatter(num) {
            let numString = String(num)
            const [integerPart, decimalPart] = numString.split('.');
            const formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            if(decimalPart == '00') {
                return `${formattedIntegerPart}`
            } else {
                return `${formattedIntegerPart}.${decimalPart}`;
            }
        },
        brouseRow(payload) {
            this.$emit('brouse', payload)
        },
        editRow(payload) {
            if(this.user.role != 'user' && this.user.role != 'observer' && this.canEdit.statement) {
                this.$emit('edit', payload)
            }
        },
        getSearchResults(ev, subject) {
            let subject_case = ''

            switch(subject) {
                case 'out_num':
                    subject_case = 'out_num'
                    break;
                case 'from_who':
                    subject_case = 'from_who'
                    break;
            }

            let data = {
                subject: subject_case,
                text: ev.target.value
            }
            
            this.$emit('update', data)
        },
        archiveRow(id) {
            if(this.user.role != 'user' && this.user.role != 'observer' && this.canEdit.statement) {
                this.$store.dispatch('agreementLog/archiveAgreementLogEntry', id).then((res) => {
                    if (res.data.success) {
                        app.notify({
                            text: `<div class="d-flex gap-2"><div>Запись успешно удалена<br><span style="font-size: 12px">` + (res.data.payload ? `Номер договора: ` + res.data.payload + `</span></div><div><svg width="26" height="26" class="mt-2 ms-3"><use xlink:href="/assets/img/i.svg#trash"></use></svg></div></div>` : ``),
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'red'
                        })
                        this.$emit('update', null)
                    } else {
                        app.notify({
                            text: 'Что-то пошло не так',
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'red'
                        })
                    }
                })
            }
        },
        unarchiveRow(id) {
            if(this.user.role != 'user' && this.user.role != 'observer' && this.canEdit.statement) {
                this.$store.dispatch('agreementLog/unarchiveAgreementLogEntry', id).then((res) => {
                    if (res.data.success) {
                        app.notify({
                            text: `<div class="d-flex gap-2"><div>Запись восстановлена<br><span style="font-size: 12px">` + (res.data.payload ? `Номер договора: ` + res.data.payload + `</span></div><div><svg width="26" height="26" class="mt-2 ms-3"><use xlink:href="/assets/img/i.svg#trash"></use></svg></div></div>` : ``),
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'green'
                        })
                        this.$emit('update')
                    } else {
                        app.notify({
                            text: 'Что-то пошло не так',
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: 'red'
                        })
                    }
                })
            }
        },
        sorting(value) {
            this.$emit('sorting', value)
        }
    }
}
</script>
<style scoped>
    .table {
        font-size: 12px;   
        margin-bottom: 0 !important;
    }
    .markdown>table>:not(caption)>*>*, .table>:not(caption)>*>* {
        padding: 0
    }
    .thead {
        color: #13081740;
    }
    .options-box {
        /* width: 140px;
        height: 80px; */
        z-index: 10;
        box-shadow: 0px 16px 64px -4px #16081E14;
    }
    .sorting_element::-webkit-input-placeholder {
        color: #13081740;
        font-weight: 600;
    }
    .sorting_element::-moz-placeholder {
        color: #13081740;
        font-weight: 600;
    }
    .sorting_element:-ms-input-placeholder {
        color: #13081740;
        font-weight: 600;
    }
    .sorting_element:-o-input-placeholder {
        color: #13081740;
        font-weight: 600;
    }
    .sorting_element:focus {
        outline: 1px solid gray;
        border-radius: 8px;
        padding: 5px;
    }
    .actions:hover {
        background-color: #F8F7F8;
    }
    .grid-row {
        border-bottom: 1px solid #1308170D;
        font-size: 12px;
        display: grid;
        gap: 0.3rem;
        grid-template-columns: 
            180px 
            minmax(110px, 140px)
            minmax(100px, 150px) /* contractor */
            minmax(120px, 150px) /* implementor */
            minmax(110px, 140px) /* agreement_num */
            minmax(60px, 120px) /*  */
            minmax(110px, 140px) /*  */
            minmax(110px, 140px) /* execution_date */
            minmax(150px, 220px) /* agreement_type */
            minmax(150px, 140px) /* agreement_title */
            minmax(180px, 200px) /* disagreement_num */
            minmax(180px, 230px) /* specification_num */
            minmax(150px, 220px) /* employee */
            minmax(100px, 140px) /* remark */
            minmax(100px, 200px) /* files */
            58px;
        justify-content: space-between;
    }
    .text-secondary {
        color: #13081766
    }
    .options-btn:hover {
        background-color: #13081708;
    }
</style>
