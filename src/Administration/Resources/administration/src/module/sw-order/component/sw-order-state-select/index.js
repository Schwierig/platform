import { Component } from 'src/core/shopware';
import './sw-order-state-select.scss';
import template from './sw-order-state-select.html.twig';

Component.register('sw-order-state-select', {
    template,
    props: {
        transitionOptions: {
            type: Array,
            required: true,
            default() {
                return [];
            }
        },
        roundedStyle: {
            type: Boolean,
            required: false,
            default: false
        },
        placeholder: {
            type: String,
            required: false,
            default: null
        },
        backgroundStyle: {
            type: String,
            required: false,
            default: ''
        }
    },
    data() {
        return {
            selectedActionName: null
        };
    },
    computed: {
        selectStyle() {
            return `sw-order-state-select__field${this.roundedStyle ? '--rounded' : ''}`;
        },
        selectPlaceholder() {
            if (this.placeholder) {
                return this.placeholder;
            }
            return this.$tc('sw-order.stateCard.labelSelectStatePlaceholder');
        }
    },
    watch: {
        selectedActionName() {
            if (this.selectedActionName !== null) {
                this.onStateChangeClicked();
            }
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {

        },
        onStateChangeClicked() {
            this.$emit('state-selected', this.selectedActionName);
            this.selectedActionName = null;
        }
    }
});
