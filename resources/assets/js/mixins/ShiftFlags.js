export default {
    data() {
        return {
            flagsObject: {
                'added': {
                    'text': 'Manually Added',
                    'icon': 'fa fa-plus-circle mr-1',
                    'color': '#FC4B6C',
                },
                'converted': {
                    'text': 'Copied from Schedule',
                    'icon': 'fa fa-calendar-plus-o mr-1',
                    'color': '#FFB22B',
                },
                'duplicate': {
                    'text': 'Potential Duplicate',
                    'icon': 'fa fa-files-o mr-1',
                    'color': 'red',
                },
                'modified': {
                    'text': 'Modified',
                    'icon': 'fa fa-pencil mr-1',
                    'color': 'orange',
                },
                // 'outside_auth': 'Outside Service Auth',
                'time_excessive': {
                    'text': 'Excessive Length',
                    'icon': 'fa fa-clock-o mr-1',
                    'color': 'red',
                }
            },
        }
    },

    computed: {
        shiftFlags() {
            return Object.keys(this.flagTypes);
        },
        flagColors() {
            return _.mapValues(this.flagsObject, item => item.color);
        },
        flagIcons() {
            return _.mapValues(this.flagsObject, item => item.icon);
        },
        flagTypes() {
            return _.mapValues(this.flagsObject, item => item.text);
        },
    },

}