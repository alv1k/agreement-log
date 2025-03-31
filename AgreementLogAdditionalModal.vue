<template>
    <div
        id="modal"
        class="new-modal modal modal-bg fade show d-block "
        tabindex="-1"
        aria-modal="true"
        role="dialog"
        @click="closeFilterModal"
    >
        <div class="position-absolute bg-white border rounded-4" :style="setPosition()">
            <div @click.stop style="padding: 0; height: 85%;">
                <div class="px-3 py-2">
                    <div class="d-flex justify-content-between">
                        <span>Группы</span>
                        <span v-if="filter_type == 'entity'" @click="setSelection('clear', 'entity', 'all')">Очистить</span>
                        <span v-if="filter_type == 'type'" @click="setSelection('clear', 'type', 'all')">Очистить</span>
                        <span v-if="filter_type == 'implementor'" @click="setSelection('clear', 'implementor', 'all')">Очистить</span>
                    </div>
                    <div class="my-3 input-icon gap-2" style="margin-left: -5px;">
                        <span class="input-icon-addon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <use xlink:href="/assets/img/i.svg#search" transform="scale(0.9, 0.9)"/>
                            </svg>
                        </span>
                        <input v-model="input_types_search" type="text" autocomplete="off" class="py-2 border border-light rounded-3" placeholder="Поиск" style="padding-left: 36px;">
                    </div>
                    <div v-if="filter_type == 'entity'">
                        <div v-for="el in legal_entity_list" :key="el.id" class="d-flex align-items-middle" style="margin-top: 0.6rem; margin-bottom: 0.6rem" @change="setSelection('add', 'entity', el.id)">
                            <input :id="'type' + el.id" :checked="selected_entity.includes(el.id)" class="form-check-input" type="checkbox" style="width: 20px; height: 20px;"/>
                            <label :for="'type' + el.id" class="form-check-label ms-2 fw-semibold hidden-with-dots" style="font-size: 15px;">{{ el.name }}</label>
                        </div>
                    </div>
                    <div v-if="filter_type == 'type'">
                        <div v-for="el in types" :key="el.id" class="d-flex align-items-middle" style="margin-top: 0.6rem; margin-bottom: 0.6rem" @change="setSelection('add', 'type', el.id)">
                            <input :id="'type' + el.id" :checked="selected_types.includes(el.id)" class="form-check-input" type="checkbox" style="width: 20px; height: 20px;"/>
                            <label :for="'type' + el.id" class="form-check-label ms-2 fw-semibold hidden-with-dots" style="font-size: 15px;">{{ el.name }}</label>
                        </div>
                    </div>
                    <div v-if="filter_type == 'implementor'">
                        <div v-for="el in legal_entity_list" :key="el.id" class="d-flex align-items-middle" style="margin-top: 0.6rem; margin-bottom: 0.6rem" @change="setSelection('add', 'implementor', el.id)">
                            <input :id="'implementor' + el.id" :checked="selected_implementor.includes(el.id)" class="form-check-input" type="checkbox" style="width: 20px; height: 20px;"/>
                            <label :for="'implementor' + el.id" class="form-check-label ms-2 fw-semibold hidden-with-dots" style="font-size: 15px;">{{ el.name }}</label>
                        </div>
                        <div class="d-flex align-items-middle" style="margin-top: 0.6rem; margin-bottom: 0.6rem" @change="setSelection('add', 'implementor', 6)">
                            <input :id="'implementor0'" :checked="selected_implementor.includes(6)" class="form-check-input" type="checkbox" style="width: 20px; height: 20px;"/>
                            <label :for="'implementor0'" class="form-check-label ms-2 fw-semibold hidden-with-dots" style="font-size: 15px;">Поставщик/подрядчик</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-bottom-side text-center py-3" style="height: 15%; background-color: #F8F7F8" @click.stop>
                <button class="btn btn-purple p-1 align-middle rounded-3" style="width: 93%;" @click="setAgreementLog()">
                    Применить
                </button>
            </div>
        </div>
    </div>
</template>
<script setup>
module.exports = {
    data() {
        return {
            modalWidth: 346,
            modalHeight: 383,
        }
    },
    props: {
        event_data: {
            type: Object,
        },
        input_types_search: {
            type: String
        },
        types: {
            type: Object,
        },
        legal_entity_list: {
            type: Object,
        },
        selected_types: {
            type: Array
        },
        selected_entity: {
            type: Array
        },
        selected_implementor: {
            type: Array
        },
        filter_type: {
            type:String
        },
    },
    methods: {        
        setSelection(action, array_name, id) {
            if(action == 'add') {
                switch(array_name) {
                    case 'type':
                        if(this.selected_types.includes(id)) {           
                            this.selected_types.splice(this.selected_types.indexOf(id), 1);
                        } else {
                            this.selected_types.push(id)
                        }
                        break;
                    case 'entity':
                        if(this.selected_entity.includes(id)) {           
                            this.selected_entity.splice(this.selected_entity.indexOf(id), 1)
                        } else {
                            this.selected_entity.push(id)
                        }
                        break;
                    case 'implementor':
                        if(this.selected_implementor.includes(id)) {           
                            this.selected_implementor.splice(this.selected_implementor.indexOf(id), 1)
                        } else {
                            this.selected_implementor.push(id)
                        }
                        break;
                }                
            } else if(action == 'clear') {
                switch(array_name) {
                    case 'type':
                        this.selected_types.splice(0, this.selected_types.length)
                        break;
                    case 'entity':
                        this.selected_entity.splice(0, this.selected_entity.length)
                        break;
                    case 'implementor':
                        this.selected_implementor.splice(0, this.selected_implementor.length)
                        break;
                }
            }         
        },
        closeFilterModal() {
            this.$emit('close_filer_modal')
        },
        setAgreementLog() {
            this.$emit('set_agreement_log')
            this.$emit('close_filer_modal')
        },
        setPosition() {
            if(this.event_data && !this.$store.state.mobileView) {                          
                return `width: ` + this.modalWidth + `px;height: ` + this.modalHeight +`px;gap: 8px; top:${this.event_data.offsetTop + (this.event_data.clientHeight)}px; left:${this.event_data.offsetLeft}px`
            } else if(this.$store.state.mobileView) {
                return `width: ` + this.modalWidth + `px;height: ` + this.modalHeight +`px;gap: 8px; top: ${window.innerHeight-this.modalHeight*1.5}px; left: ${window.innerWidth/2-this.modalWidth/2}px`
                console.log(this.modalHeight);
                
                return `width: ` + this.modalWidth + `px;height: ` + this.modalHeight +`px;gap: 8px; top: ${window.innerHeight/2-this.modalHeight/2}px; left: ${window.innerWidth/2-this.modalWidth/2}px`
            }
        }
    }
}

</script>
<style scoped>
    
</style>