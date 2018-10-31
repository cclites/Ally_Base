<template>
    <select class="form-control">
        <slot></slot>
    </select>
</template>

<script>
    export default {
        props: ['options', 'value'],


        mounted() {
            this.initSelect();
            this.fixInsideModal();
        },
        data() {
            return {}
        },
        computed: {
            allOptions() {
                return {
                    width: '100%',
                    ...this.options || {},
                }
            }
        },
        methods: {
            initSelect() {
                let vm = this;
                $(this.$el).select2(this.allOptions)
                        .val(this.value)
                        .trigger('change')
                        .on('change', function() {
                            vm.$emit('input', this.value)
                        });
            },
            fixInsideModal() {
                $(this.$el).closest('.modal-content').attr('tabindex', false);
            },
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value);
            },
            options: function (options) {
                this.initSelect();
            }
        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    }
</script>
