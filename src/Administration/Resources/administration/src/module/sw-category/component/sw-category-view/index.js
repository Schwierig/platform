import { Component, Mixin, State } from 'src/core/shopware';
import template from './sw-category-view.html.twig';
import './sw-category-view.scss';

Component.register('sw-category-view', {
    template,

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    props: {
        category: {
            type: Object,
            required: true,
            default: {}
        },
        mediaItem: {
            type: Object,
            required: false,
            default: null
        },
        isLoading: {
            type: Boolean,
            required: true,
            default: false
        }
    },

    data() {
        return {
            products: [],
            entityName: 'product',
            sortBy: 'name',
            sortDirection: 'ASC',
            isLoadingProducts: false,
            deleteButtonDisabled: true
        };
    },

    computed: {
        productStore() {
            return State.getStore('product');
        },

        categoryProductStore() {
            return this.category.getAssociation('products');
        }
    },

    methods: {
        getList() {
            this.isLoadingProducts = true;
            const params = this.getListingParams();

            return this.categoryProductStore.getList(params).then(response => {
                this.total = response.total;
                this.products = response.items;
                this.isLoadingProducts = false;
                this.buildGridArray();
                return this.products;
            });
        },

        onChangeLanguage() {
            this.getList();
        },

        buildGridArray() {
            this.products = this.products.filter(value => value.isLocal === false);
            this.products.splice(0, 0, ...this.getNewItems());
        },

        getNewItems() {
            const newProducts = [];
            this.categoryProductStore.forEach((product) => {
                if (product.isLocal) {
                    newProducts.push(product);
                }
            });
            return newProducts;
        },

        onResetAssociations() {
            this.$refs.productAssignmentSelect.resetAssociations();
        },

        onUploadAdded({ uploadTag }) {
            this.$emit('sw-category-view-on-upload-media', uploadTag);
        },

        setMediaItem({ media }) {
            this.$emit('sw-category-view-on-set-media', media);
        },

        removeMediaItem() {
            this.$emit('sw-category-view-on-remove-media');
        },

        openMediaSidebar() {
            this.$emit('sw-category-view-open-sidebar');
        },

        onViewProduct(productId) {
            this.$router.push({ name: 'sw.product.detail', params: { id: productId } });
        },

        onSelectProduct(productId) {
            if (this.products.find(product => product.id === productId)) {
                this.onRemoveDuplicate(productId);
            } else {
                this.addProduct(productId);
            }
        },

        addProduct(productId) {
            this.$refs.productAssignmentSelect.addToAssociations(productId);
            const product = this.productStore.getById(productId);

            if (!this.categoryProductStore.hasId(productId)) {
                const newProduct = this.categoryProductStore.create(productId);
                newProduct.setData(product);
                newProduct.isLocal = true;
                this.products.push(newProduct);
            }

            this.buildGridArray();
        },

        selectionChanged() {
            const selection = this.$refs.grid.getSelection();
            this.deleteButtonDisabled = Object.keys(selection).length <= 0;
        },

        onRemoveProducts() {
            const selection = this.$refs.grid.getSelection();
            Object.values(selection).forEach(product => {
                this.onRemoveProduct(product, true);
                this.$refs.grid.selectItem(false, product);
            });
        },

        onRemoveDuplicate(productId) {
            const product = this.products.find(match => match.id === productId);
            this.onRemoveProduct(product);
        },

        onRemoveProduct(product, ignoreUndo = false) {
            this.$refs.productAssignmentSelect.removeFromAssociations(product.id);

            if (product.isDeleted && !ignoreUndo) {
                product.isDeleted = false;
                return;
            }

            if (product.isLocal) {
                this.categoryProductStore.removeById(product.id);
                product.delete();
                this.removeSelection(product.id);
            } else {
                product.isDeleted = true;
            }

            this.buildGridArray();
        },

        removeSelection(productId) {
            this.products.forEach((item, index) => {
                if (item.id === productId) {
                    this.products.splice(index, 1);
                }
            });
        }
    }
});
