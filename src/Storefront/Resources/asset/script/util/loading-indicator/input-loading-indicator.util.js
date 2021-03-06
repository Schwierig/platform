import LoadingIndicatorUtil from 'asset/script/util/loading-indicator/loading-indicator.util';

export default class InputLoadingIndicatorUtil extends LoadingIndicatorUtil {

    /**
     * Constructor
     * @param {Element|string} parent
     * @param position
     */
    constructor(parent, position = 'before') {
        super(parent, position);

        if (this._isInputElement() === false) {
            throw Error('Parent element is not of type <input>');
        }
    }

    /**
     * Insert the loading indicator after the input field
     */
    create() {
        if (this.exists()) return;
        this.parent.insertAdjacentHTML('afterend', LoadingIndicatorUtil.getTemplate());
    }

    /**
     * remove loading indicators
     */
    remove() {
        const indicators = this.parent.parentNode.querySelectorAll(`.${LoadingIndicatorUtil.SELECTOR_CLASS()}`);
        indicators.forEach(indicator => indicator.remove());
    }

    /**
     * Verify whether the injected parent is of type <input> or not
     * @returns {boolean}
     * @private
     */
    _isInputElement() {
        return (this.parent.tagName.toLowerCase() === 'input');
    }
}
