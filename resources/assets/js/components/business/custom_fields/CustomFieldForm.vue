<template>
    <div>
        <h3>Custom Fields</h3>
        <b-form-group 
            v-for="field in fields"
            :label="field.label" 
            :label-for="field.key" 
            :label-class="field.required ? 'required' : ''"
            :key="field.id"
        >
            <b-form-input 
                v-if="field.type == 'input'"
                :id="field.key"
                :name="field.key"
                type="text"
                v-model="form[field.key]"
                :required="!!field.required || null"
            />

            <b-form-textarea
                v-if="field.type == 'textarea'"
                :id="field.key"
                :name="field.key"
                v-model="form[field.key]"
                rows="5"
                :required="!!field.required || null"
            />

            <b-form-select 
                v-if="field.type == 'dropdown'"
                :id="field.key"
                :name="field.key"
                v-model="form[field.key]"
                :options="[dropdownDefault, ...field.options]"
            />

            <b-form-radio-group
                v-if="field.type == 'radio'"
                v-model="form[field.key]" 
                :name="field.key" 
                :required="!!field.required || null"
            >
                <b-form-radio :value="1">Yes</b-form-radio>
                <b-form-radio :value="0">No</b-form-radio>
            </b-form-radio-group>
        </b-form-group>
    </div>
</template>

<script>
export default {
    props: {
        form: {
            type: Object,
            required: true,
        },
        fields: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            dropdownDefault: {
                value: '',
                text: '--- Select ---',
                disabled: true,
            },
        }; 
    }
}
</script>
