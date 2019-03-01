<template>
    <confirmation-modal v-model="localValue" confirm-text="Save" confirm-variant="info" cancel-text="Cancel" @confirm="$emit('submit', selected)">
        <p>
            This schedule was created as a part of a recurring group: {{ groupData.rule_text }}.
        </p>

        <b-form-group label="Save recurring group" label-class="font-weight-bold">
            <b-form-radio-group v-model="selected"
                                stacked
                                name="radiosStacked">
                <b-form-radio value="single">
                    Only this selected day <small class="text-muted">1 occurrence</small>
                </b-form-radio>
                <b-form-radio value="future_weekday" v-if="!showTotalOption || futureWeekdayOccurrences !== futureAllOccurrences">
                    This day and future related {{ weekdayText }}. <small class="text-muted">{{ futureWeekdayOccurrences }} occurrences</small>
                </b-form-radio>
                <b-form-radio value="future_all" v-if="showTotalOption">
                    This day and all related future days. <small class="text-muted">{{ futureAllOccurrences }} occurrences</small>
                </b-form-radio>
                <b-form-radio value="total_weekday" v-if="totalWeekdayOccurrences !== totalAllOccurrences && (!showTotalOption || totalWeekdayOccurrences !== futureWeekdayOccurrences)">
                    All past and future {{ weekdayText }} <small class="text-muted">{{ totalWeekdayOccurrences }} occurrences</small>
                </b-form-radio>
                <b-form-radio value="total_all" v-if="totalAllOccurrences !== futureAllOccurrences && showTotalOption">
                    All past and future occurrences <small class="text-muted">{{ totalAllOccurrences }} occurrences</small>
                </b-form-radio>
            </b-form-radio-group>
        </b-form-group>

        <small class="text-muted" v-if="selected === 'single'">Note: Your choice will separate this occurrence from the recurring group.</small>
        <small class="text-muted" v-else-if="forkWarning">Note: Your choice will split the original group into two groups for future updates.</small>
    </confirmation-modal>
</template>

<script>
    import ConfirmationModal from "./ConfirmationModal";

    export default {
        name: "ScheduleGroupModal",
        components: {ConfirmationModal},
        props: ['value', 'weekdayInt', 'groupData', 'dayChange'],
        computed: {
            localValue: {
                get() {
                    return this.value;
                },
                set(value) {
                    this.$emit('input', value)
                }
            },
            weekdayText() {
                const weekDays = {
                    0: 'Sundays',
                    1: 'Mondays',
                    2: 'Tuesdays',
                    3: 'Wednesdays',
                    4: 'Thursdays',
                    5: 'Fridays',
                    6: 'Saturdays',
                };
                return weekDays[this.weekdayInt];
            },
            futureAllOccurrences() {
                return parseInt(this.groupData.future_schedules) || 0;
            },
            futureWeekdayOccurrences() {
                return parseInt(this.groupData.future_schedules_by_weekday[this.weekdayInt]) || 0;
            },
            totalAllOccurrences() {
                return parseInt(this.groupData.total_schedules) || 0;
            },
            totalWeekdayOccurrences() {
                return parseInt(this.groupData.total_schedules_by_weekday[this.weekdayInt]) || 0;
            },
            showTotalOption() {
                return this.groupData.interval_type === 'monthly' || !this.dayChange;
            },
            forkWarning() {
                return !(this.selected === 'total_all'
                    || (this.selected === 'future_all' && (this.dayChange || this.totalAllOccurrences === this.futureAllOccurrences)));
            }
        },

        data() {
            return {
                selected: 'single',
            }
        }
    }
</script>

<style scoped>
    .bolder {
        font-weight: 500;
    }
</style>