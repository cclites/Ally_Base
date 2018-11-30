export default {
    data() {
        return {
            flagTypes: {
                'added': 'Manually Added',
                'converted': 'Copied from Schedule',
                'duplicate': 'Potential Duplicate',
                'modified': 'Modified',
                // 'outside_auth': 'Outside Service Auth',
                'time_excessive': 'Excessive Length'
            },
            flagIcons: {
                'added': 'fa fa-plus-circle mr-1',
                'converted': 'fa fa-calendar-plus-o mr-1',
                'duplicate': 'fa fa-files-o mr-1',
                'modified': 'fa fa-pencil mr-1',
                'outside_auth': 'fa fa-outdent mr-1',
                'time_excessive': 'fa fa-clock-o mr-1',
            }
        }
    },

    computed: {
        shiftFlags() {
            return Object.keys(this.flagTypes);
        }
    },

}