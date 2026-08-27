import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

/*
 * "Send a custom metric" form: increment() and decrement() don't take a
 * value, so the value field is only useful (and required) for measure(),
 * timing() and gauge(). This purely cosmetic toggle keeps the form usable
 * without JavaScript too: the server ignores the value for increment and
 * decrement no matter what is submitted.
 */
const OPERATIONS_WITHOUT_VALUE = ['increment', 'decrement'];

function initSendMetricForm() {
    const form = document.querySelector('[data-send-metric-form]');
    if (!form) {
        return;
    }

    const operationSelect = form.querySelector('[name="operation"]');
    const valueField = form.querySelector('[data-value-field]');
    const valueInput = form.querySelector('[name="value"]');
    const helpTexts = form.querySelectorAll('[data-operation-help]');

    const syncOperation = () => {
        const needsValue = !OPERATIONS_WITHOUT_VALUE.includes(operationSelect.value);
        valueField.classList.toggle('d-none', !needsValue);
        valueInput.disabled = !needsValue;
        valueInput.required = needsValue;

        helpTexts.forEach((el) => {
            el.hidden = el.dataset.operationHelp !== operationSelect.value;
        });
    };

    operationSelect.addEventListener('change', syncOperation);
    syncOperation();
}

document.addEventListener('DOMContentLoaded', initSendMetricForm);
