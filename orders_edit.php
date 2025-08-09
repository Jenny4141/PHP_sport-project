<?php
include __DIR__ . '/parts/init.php';

$title = '編輯訂單';
$pageName = 'orders_edit';

$id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($id <= 0) {
    header('Location: orders_list.php');
    exit;
}

$sqlOrder = "SELECT * FROM orders WHERE order_id= ?";
$stmtOrder = $pdo->prepare($sqlOrder);
$stmtOrder->execute([$id]);
$r = $stmtOrder->fetch(PDO::FETCH_ASSOC);

if (empty($r)) {
    header('Location: orders_list.php');
    exit;
}

$optsPayment = [
    '信用卡' => '信用卡',
    'Line Pay' => 'Line Pay',
    '貨到付款' => '貨到付款',
];

$optsDelivery = [
    '宅配' => ['display' => '宅配', 'fee' => 80],
    '7-11' => ['display' => '7-11 超商取貨', 'fee' => 60],
    '全家' => ['display' => '全家 超商取貨', 'fee' => 60],
];

$optsInvoice = [
    '統一發票' => '統一發票',
    '統編' => '統編',
    '載具' => '載具',
];

$optsStatus = [
    '待出貨' => '待出貨',
    '已出貨' => '已出貨',
    '已完成' => '已完成',
];

$sqlItems = "SELECT
                oi.item_id, oi.order_id, oi.spec_id, oi.quantity, oi.price,
                oi.ordered_product_name,
                oi.ordered_spec_color,
                oi.ordered_product_image,
                oi.item_status,
                s.product_id AS current_product_id_from_spec
            FROM order_items oi
            LEFT JOIN specs s ON oi.spec_id = s.spec_id
            WHERE oi.order_id = ?
            ORDER BY oi.item_id";
$stmtItems = $pdo->prepare($sqlItems);
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$sqlProducts = "SELECT product_id, name FROM products ORDER BY name";
$stmtProducts = $pdo->query($sqlProducts);
$jsProducts = $stmtProducts->fetchAll(PDO::FETCH_KEY_PAIR);

$sqlSpecs = "SELECT spec_id, product_id, color, size, price FROM specs ORDER BY product_id, color, size";
$stmtSpecs = $pdo->query($sqlSpecs);
$dbSpecs = $stmtSpecs->fetchAll(PDO::FETCH_ASSOC);

$jsSpecsByProd = [];
foreach ($dbSpecs as $spec) {
    $jsSpecsByProd[$spec['product_id']][] = $spec;
}

foreach ($items as $key => $item) {
    $items[$key]['current_product_id_for_select'] = $item['current_product_id_from_spec'];
    if (empty($items[$key]['current_product_id_for_select']) && !empty($item['spec_id'])) {
        foreach ($jsSpecsByProd as $prodIdLoop => $specsArray) {
            foreach ($specsArray as $specLoop) {
                if ($specLoop['spec_id'] == $item['spec_id']) {
                    $items[$key]['current_product_id_for_select'] = $prodIdLoop;
                    break 2;
                }
            }
        }
    }
    $items[$key]['current_spec_id_for_select'] = $item['spec_id'];
}

?>

<?php include __DIR__ . '/parts/html-head.php' ?>
<script id="jsProductsData" type="application/json">
    <?= json_encode($jsProducts, JSON_UNESCAPED_UNICODE) ?>
</script>
<script id="jsSpecsByProdData" type="application/json">
    <?= json_encode($jsSpecsByProd, JSON_UNESCAPED_UNICODE) ?>
</script>

<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>
<style>
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545;
    }

    #itemsTbody td {
        position: relative;
    }

    #itemsTbody .invalid-feedback {
        font-size: .75em;
    }

    .modal-body .alert-success,
    .modal-body .alert-warning {
        display: none;
    }
</style>

<div class="container px-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-9">
            <div class="card mb-5">
                <div class="card-body">
                    <h5 class="card-title">編輯訂單 (訂單編號: <?= htmlentities($r['order_id']) ?>)</h5>
                    <form id="orderForm" name="form1" novalidate>
                        <input type="hidden" name="order_id" value="<?= htmlentities($r['order_id']) ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">會員ID</label>
                                <input type="text" class="form-control" value="<?= htmlentities($r['member_id']) ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delivery" class="form-label">物流方式</label>
                                <select class="form-select" id="delivery" name="delivery" data-required="true">
                                    <option value="">請選擇物流方式</option>
                                    <?php foreach ($optsDelivery as $val => $details): ?>
                                        <option value="<?= htmlentities($val) ?>" data-fee="<?= $details['fee'] ?>" <?= ($r['delivery'] === $val) ? 'selected' : '' ?>><?= htmlentities($details['display']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">物流方式為必填</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fee" class="form-label">運費</label>
                                <input type="number" class="form-control" id="fee" name="fee" value="<?= htmlentities($r['fee']) ?>" data-required="true" min="0" step="1" readonly>
                                <div class="invalid-feedback">運費為必填且不可為負數</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment" class="form-label">付款方式</label>
                                <select class="form-select" id="payment" name="payment" data-required="true">
                                    <option value="">請選擇付款方式</option>
                                    <?php foreach ($optsPayment as $val => $txt): ?>
                                        <option value="<?= htmlentities($val) ?>" <?= ($r['payment'] === $val) ? 'selected' : '' ?>><?= htmlentities($txt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">付款方式為必填</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">住址</label>
                            <textarea class="form-control" id="address" name="address" rows="3" data-required="true"><?= htmlentities($r['address']) ?></textarea>
                            <div class="invalid-feedback">住址為必填</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="invoice" class="form-label">發票類型</label>
                                <select class="form-select" id="invoice" name="invoice" data-required="true">
                                    <option value="">請選擇發票類型</option>
                                    <?php foreach ($optsInvoice as $val => $txt): ?>
                                        <option value="<?= htmlentities($val) ?>" <?= ($r['invoice'] === $val) ? 'selected' : '' ?>><?= htmlentities($txt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">發票類型為必填</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">訂單狀態</label>
                                <select class="form-select" id="status" name="status" data-required="true">
                                    <option value="">請選擇狀態</option>
                                    <?php foreach ($optsStatus as $val => $txt): ?>
                                        <option value="<?= htmlentities($val) ?>" <?= ($r['status'] === $val) ? 'selected' : '' ?>><?= htmlentities($txt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">訂單狀態為必填</div>
                            </div>
                        </div>

                        <h5 class="mt-4">商品明細</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead>
                                    <tr>
                                        <th>商品名稱</th>
                                        <th>款式</th>
                                        <th>單價</th>
                                        <th>數量</th>
                                        <th>刪除</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTbody">
                                    <?php foreach ($items as $idx => $item): ?>
                                        <tr data-item-id="<?= htmlspecialchars($item['item_id'] ?? '') ?>" class="existing-item-row"
                                            data-item-status="<?= htmlspecialchars($item['item_status'] ?? 'active') ?>">
                                            <td>
                                                <?php if ($item['item_status'] === 'product_removed' || $item['item_status'] === 'spec_removed'): ?>
                                                    <?= htmlspecialchars($item['ordered_product_name'] ?: 'N/A') ?>
                                                    <span class="badge bg-<?= ($item['item_status'] === 'product_removed') ? 'danger' : 'warning text-dark' ?> ms-1">
                                                        <?= ($item['item_status'] === 'product_removed') ? '商品已停售' : '規格已下架' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <select class="form-select product-select"
                                                        name="items[<?= $item['item_id'] ?>][product_id_display_not_submitted]"
                                                        data-current-prod-id="<?= htmlspecialchars($item['current_product_id_for_select'] ?? '') ?>"
                                                        data-required="true" required>
                                                        <option value="">選擇商品</option>
                                                    </select>
                                                    <div class="invalid-feedback text-start">請選擇商品</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($item['item_status'] === 'product_removed' || $item['item_status'] === 'spec_removed'): ?>
                                                    <?= htmlspecialchars($item['ordered_spec_color'] ?: 'N/A') ?>
                                                <?php else: ?>
                                                    <select class="form-select spec-select"
                                                        name="items[<?= $item['item_id'] ?>][spec_id]"
                                                        data-current-spec-id="<?= htmlspecialchars($item['current_spec_id_for_select'] ?? '') ?>"
                                                        data-required="true" required>
                                                        <option value="">選擇款式</option>
                                                    </select>
                                                    <div class="invalid-feedback text-start">請選擇款式</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                $<span class="price-display"><?= number_format($item['price'] ?? 0, 0) ?></span>
                                                <input type="hidden" class="price-input" name="items[<?= $item['item_id'] ?>][price_at_order_dummy_not_submitted]" value="<?= $item['price'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control item-quantity"
                                                    name="items[<?= $item['item_id'] ?>][quantity]"
                                                    min="1" value="<?= $item['quantity'] ?? 1 ?>" required
                                                    <?= ($item['item_status'] === 'product_removed' || $item['item_status'] === 'spec_removed') ? 'disabled' : '' ?>>
                                                <div class="invalid-feedback text-start">請輸入有效數量</div>
                                            </td>
                                            <td class="sm-td">
                                                <a href="#" class="delete-btn text-danger">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-success btn-sm mb-3" id="addItemBtn"><i class="fa-solid fa-plus"></i> 新增商品</button>

                        <div class="mt-3">
                            <label for="itemsSubtotalDisplay" class="form-label">商品總金額</label>
                            <input type="text" class="form-control" id="itemsSubtotalDisplay" value="$0" disabled>
                        </div>
                        <div class="mt-3">
                            <label for="finalTotalDisplay" class="form-label">訂單應付金額</label>
                            <input type="text" class="form-control" id="finalTotalDisplay" value="$0" disabled>
                        </div>
                        <div class="mt-3 col-12 col-md-10">
                            <button type="submit" class="btn btn-primary" id="submitOrderBtn">修改</button>
                            <a href="orders_list.php" class="btn btn-outline-secondary ms-2">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">編輯結果</h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" role="alert">成功編輯訂單！</div>
                <div class="alert alert-warning" role="alert">沒有資料修改</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續編輯</button>
                <a id="backToList" class="btn btn-primary" href="orders_list.php">回列表頁</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/parts/html-scripts.php' ?>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const products = JSON.parse(document.getElementById('jsProductsData').textContent);
        const specsByProduct = JSON.parse(document.getElementById('jsSpecsByProdData').textContent);

        const form = document.getElementById("orderForm");
        const modalElement = document.getElementById("exampleModal");
        const modal = new bootstrap.Modal(modalElement);
        const modalTitleEl = document.getElementById("exampleModalLabel");
        const modalBodyEl = modalElement.querySelector(".modal-body");
        const modalFooterEl = modalElement.querySelector(".modal-footer");
        const successAlert = modalBodyEl.querySelector(".alert-success");
        const warningAlert = modalBodyEl.querySelector(".alert-warning");

        const itemsTbody = document.getElementById("itemsTbody");
        const addItemBtn = document.getElementById("addItemBtn");
        const submitBtn = document.getElementById("submitOrderBtn");
        let newItemIdx = 0;

        const itemsSubtotalEl = document.getElementById("itemsSubtotalDisplay");
        const feeEl = document.getElementById("fee");
        const finalTotalDisplayEl = document.getElementById("finalTotalDisplay");

        const mainInputs = form.querySelectorAll('[data-required="true"]');
        const deliveryEl = document.getElementById("delivery");

        function updateFinalTotalDisplay() {
            if (!itemsSubtotalEl || !feeEl || !finalTotalDisplayEl) return;
            const subtotalString = itemsSubtotalEl.value.replace(/[$,]/g, '');
            const subtotal = parseFloat(subtotalString) || 0;
            const currentFee = parseFloat(feeEl.value) || 0;
            const finalTotal = subtotal + currentFee;
            finalTotalDisplayEl.value = '$' + finalTotal.toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function updateFee() {
            if (deliveryEl && feeEl) {
                const selectedOpt = deliveryEl.options[deliveryEl.selectedIndex];
                if (selectedOpt && selectedOpt.value !== "" && typeof selectedOpt.dataset.fee !== 'undefined') {
                    feeEl.value = selectedOpt.dataset.fee;
                } else if (selectedOpt && selectedOpt.value === "") {
                    feeEl.value = 0;
                }
                validateInput(feeEl);
            }
            updateFinalTotalDisplay();
        }

        function updateItemsSubtotal() {
            let currentSubtotal = 0;
            itemsTbody.querySelectorAll('tr').forEach(row => {
                const priceInput = row.querySelector('.price-input');
                const quantityInput = row.querySelector('.item-quantity');
                if (priceInput && quantityInput && quantityInput.value && !quantityInput.disabled) {
                    const price = parseFloat(priceInput.value) || 0;
                    const quantity = parseInt(quantityInput.value) || 0;
                    if (quantity > 0) {
                        currentSubtotal += price * quantity;
                    }
                }
            });
            if (itemsSubtotalEl) {
                itemsSubtotalEl.value = '$' + currentSubtotal.toLocaleString('en-US', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
            updateFinalTotalDisplay();
        }

        function renderProdOpts(selectEl, currentProdId) {
            selectEl.innerHTML = '<option value="">選擇商品</option>';
            for (const prodId in products) {
                const option = document.createElement('option');
                option.value = prodId;
                option.textContent = products[prodId];
                if (prodId && currentProdId && prodId.toString() === currentProdId.toString()) {
                    option.selected = true;
                }
                selectEl.appendChild(option);
            }
        }

        function renderSpecOpts(specSelectEl, prodId, currentSpecId) {
            specSelectEl.innerHTML = '<option value="">選擇款式</option>';
            const priceDisplay = specSelectEl.closest('tr').querySelector('.price-display');
            const priceInput = specSelectEl.closest('tr').querySelector('.price-input');

            // 保留 PHP 渲染的 price (來自 order_items.price) 作為初始值
            // 只有當規格實際被用戶更改時，才用 specsByProduct 的價格更新
            // 所以這裡不主動重置 priceDisplay 和 priceInput
            // if (priceDisplay) priceDisplay.textContent = '0';
            // if (priceInput) priceInput.value = '0';


            if (prodId && specsByProduct[prodId]) {
                let specFoundAndSelected = false;
                specsByProduct[prodId].forEach(spec => {
                    const option = document.createElement('option');
                    option.value = spec.spec_id;
                    option.textContent = spec.color;
                    option.dataset.price = spec.price;
                    if (spec.spec_id && currentSpecId && spec.spec_id.toString() === currentSpecId.toString()) {
                        option.selected = true;
                        specFoundAndSelected = true;
                        // 初始化時，不更新 priceDisplay 和 priceInput，讓它們保持 PHP 渲染的值
                        // 價格的更新將由 change 事件觸發
                    }
                    specSelectEl.appendChild(option);
                });
                // 如果 currentSpecId 存在但在 specsByProduct 中找不到 (例如規格已刪除但 item_status 仍為 active)
                // 並且 priceInput 有值 (來自 PHP 的 order_items.price)，則保留該價格顯示
                if (currentSpecId && !specFoundAndSelected && priceInput && priceInput.value !== '0' && priceDisplay) {
                    // priceDisplay.textContent = parseFloat(priceInput.value).toLocaleString(...); // 保持 PHP 的顯示
                } else if (!specFoundAndSelected) {
                    if (priceDisplay) priceDisplay.textContent = '0';
                    if (priceInput) priceInput.value = '0';
                }
            } else {
                if (priceDisplay) priceDisplay.textContent = '0';
                if (priceInput) priceInput.value = '0';
            }
        }

        function validateInput(inputField) {
            let isValid = true;
            const feedbackEl = inputField.nextElementSibling;
            inputField.classList.remove('is-invalid');
            if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                feedbackEl.textContent = '';
            }

            if (inputField.disabled) return true;

            if (inputField.dataset.required && inputField.value.trim() === "") {
                isValid = false;
                if (feedbackEl) feedbackEl.textContent = inputField.closest('td').querySelector('label')?.textContent || inputField.closest('div.mb-3').querySelector('label')?.textContent + '為必填' || '此欄位為必填';

            }
            if (inputField.type === "number") {
                const minVal = parseFloat(inputField.min || "0");
                const currentVal = parseFloat(inputField.value);
                if (isNaN(currentVal) || currentVal < minVal) {
                    isValid = false;
                    if (feedbackEl) feedbackEl.textContent = `請輸入有效的數字 (至少 ${minVal})`;
                }
                if (inputField.id === 'fee' && currentVal < 0) {
                    isValid = false;
                    if (feedbackEl) feedbackEl.textContent = '運費不可為負數';
                }
            }
            if (inputField.tagName === "SELECT" && inputField.dataset.required && inputField.value === "") {
                isValid = false;
                if (feedbackEl) feedbackEl.textContent = '請選擇一個選項';
            }

            if (!isValid) {
                inputField.classList.add('is-invalid');
            }
            return isValid;
        }

        function checkForm() {
            let isFormValid = true;
            form.querySelectorAll('[data-required="true"]').forEach(input => {
                if (!validateInput(input)) {
                    isFormValid = false;
                }
            });

            itemsTbody.querySelectorAll('tr').forEach(row => {
                if (row.dataset.itemStatus === 'product_removed' || row.dataset.itemStatus === 'spec_removed') {
                    return;
                }
                const prodSel = row.querySelector('.product-select');
                const specSel = row.querySelector('.spec-select');
                const qtyIn = row.querySelector('.item-quantity');

                if (prodSel && !validateInput(prodSel)) isFormValid = false;
                if (specSel && !validateInput(specSel)) isFormValid = false;
                if (qtyIn && !validateInput(qtyIn)) isFormValid = false;
            });


            if (itemsTbody.querySelectorAll('tr:not([style*="display: none"])').length === 0 && itemsTbody.querySelectorAll('tr').length > 0) {
                let allRemoved = true;
                itemsTbody.querySelectorAll('tr').forEach(row => {
                    if (row.dataset.itemStatus === 'active') allRemoved = false;
                });
                if (allRemoved) {
                    warningAlert.textContent = '訂單中所有項目均已下架或停售，無法儲存。請至少新增一個有效商品。';
                    successAlert.style.display = 'none';
                    warningAlert.style.display = 'block';
                    isFormValid = false;
                }
            } else if (itemsTbody.querySelectorAll('tr').length === 0) {
                warningAlert.textContent = '訂單至少需要一個商品項目。';
                successAlert.style.display = 'none';
                warningAlert.style.display = 'block';
                isFormValid = false;
            }

            submitBtn.disabled = !isFormValid;
            return isFormValid;
        }

        itemsTbody.querySelectorAll('.existing-item-row').forEach(row => {
            const itemStatus = row.dataset.itemStatus;
            const prodSelect = row.querySelector('.product-select');
            const specSelect = row.querySelector('.spec-select');
            const quantityInput = row.querySelector('.item-quantity');

            if (itemStatus === 'product_removed' || itemStatus === 'spec_removed') {
                if (prodSelect) prodSelect.disabled = true;
                if (specSelect) specSelect.disabled = true;
                if (quantityInput) quantityInput.disabled = true;
            } else if (prodSelect && specSelect) {
                const currentProdId = prodSelect.dataset.currentProdId;
                const currentSpecId = specSelect.dataset.currentSpecId;

                renderProdOpts(prodSelect, currentProdId);
                if (currentProdId) {
                    renderSpecOpts(specSelect, currentProdId, currentSpecId);
                } else {
                    specSelect.innerHTML = '<option value="">選擇款式</option>';
                }
                // 初始化後，移除 is-invalid (如果沒有值，checkForm 會處理)
                if (prodSelect.value) prodSelect.classList.remove('is-invalid');
                if (specSelect.value) specSelect.classList.remove('is-invalid');
            }
        });

        updateFee();
        updateItemsSubtotal();
        checkForm();

        if (deliveryEl) {
            deliveryEl.addEventListener('change', function() {
                updateFee();
                checkForm();
            });
        }

        form.querySelectorAll('[data-required="true"]').forEach(input => {
            if (input.id === 'fee' || input.id === 'delivery') return;
            const eventType = (input.tagName === 'SELECT') ? 'change' : 'input';
            input.addEventListener(eventType, () => {
                validateInput(input);
                checkForm();
            });
            input.addEventListener('blur', () => {
                validateInput(input);
                checkForm();
            });
        });


        itemsTbody.addEventListener('change', function(e) {
            const target = e.target;
            const currentRow = target.closest('tr');
            if (!currentRow) return;
            let recheckFormValidation = false;

            if (target.classList.contains('product-select')) {
                const specSelectEl = currentRow.querySelector('.spec-select');
                const priceDisplayEl = currentRow.querySelector('.price-display');
                const priceInputEl = currentRow.querySelector('.price-input');
                renderSpecOpts(specSelectEl, target.value, null); // 傳遞 false 或不傳 isInitialRender
                if (priceDisplayEl) priceDisplayEl.textContent = '0';
                if (priceInputEl) priceInputEl.value = '0';
                updateItemsSubtotal();
                validateInput(target);
                if (specSelectEl) validateInput(specSelectEl);
                recheckFormValidation = true;
            } else if (target.classList.contains('spec-select')) {
                const selectedOpt = target.options[target.selectedIndex];
                const priceDisp = currentRow.querySelector('.price-display');
                const priceIn = currentRow.querySelector('.price-input');
                if (selectedOpt && selectedOpt.value && selectedOpt.dataset.price) {
                    const price = selectedOpt.dataset.price;
                    if (priceDisp) priceDisp.textContent = parseFloat(price).toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    if (priceIn) priceIn.value = price;
                } else {
                    if (priceDisp) priceDisp.textContent = '0';
                    if (priceIn) priceIn.value = '0';
                }
                updateItemsSubtotal();
                validateInput(target);
                recheckFormValidation = true;
            }
            if (recheckFormValidation) {
                checkForm();
            }
        });

        itemsTbody.addEventListener('input', function(e) {
            const target = e.target;
            if (target.classList.contains('item-quantity')) {
                updateItemsSubtotal();
                validateInput(target);
                checkForm();
            }
        });

        addItemBtn.addEventListener("click", () => {
            newItemIdx++;
            const newRow = document.createElement("tr");
            newRow.classList.add("new-item-row");
            const itemKey = `new_${newItemIdx}`;
            newRow.innerHTML = `
            <td>
                <select class="form-select product-select" name="new_items[${itemKey}][product_id_display_not_submitted]" data-required="true" required><option value="">選擇商品</option></select>
                <div class="invalid-feedback text-start">請選擇商品</div>
            </td>
            <td>
                <select class="form-select spec-select" name="new_items[${itemKey}][spec_id]" data-required="true" required><option value="">選擇款式</option></select>
                <div class="invalid-feedback text-start">請選擇款式</div>
            </td>
            <td>$<span class="price-display">0</span><input type="hidden" class="price-input" name="new_items[${itemKey}][price_at_order]" value="0"></td>
            <td>
                <input type="number" class="form-control item-quantity" name="new_items[${itemKey}][quantity]" min="1" value="1" data-required="true" required>
                <div class="invalid-feedback text-start">請輸入有效數量 (至少1)</div>
            </td>
            <td class="sm-td">
                <a href="#" class="delete-btn text-danger">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            </td>
        `;
            itemsTbody.appendChild(newRow);
            renderProdOpts(newRow.querySelector('.product-select'), null);
            updateItemsSubtotal();
            checkForm();
        });

        itemsTbody.addEventListener("click", function(e) {
            const deleteLink = e.target.closest("a.delete-btn");
            if (!deleteLink) return;
            e.preventDefault();
            const currentRow = deleteLink.closest("tr");
            const allRows = itemsTbody.querySelectorAll('tr');

            if (allRows.length <= 1) {
                restoreDefaultModalState('操作限制', '', "訂單至少需要保留一個商品項目，無法刪除最後一個商品。");
                successAlert.style.display = 'none';
                warningAlert.textContent = "訂單至少需要保留一個商品項目。";
                warningAlert.style.display = 'block';
                modalFooterEl.innerHTML = '<button type="button" class="btn btn-primary" data-bs-dismiss="modal">知道了</button>';
                modal.show();
                return;
            }

            const itemId = currentRow.dataset.itemId;
            if (itemId) {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "deleted_item_ids[]";
                hiddenInput.value = itemId;
                form.appendChild(hiddenInput);
            }
            currentRow.remove();
            updateItemsSubtotal();
            checkForm();
        });

        function restoreDefaultModalState(title, successMsg, warningMsg) {
            modalTitleEl.textContent = title || '編輯結果';
            successAlert.textContent = successMsg || '成功編輯訂單！';
            warningAlert.textContent = warningMsg || '沒有資料修改';
            modalFooterEl.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">繼續編輯</button>
                <a id="backToListModalLink" class="btn btn-primary" href="orders_list.php">回列表頁</a>`;
            const backToListModalLink = document.getElementById("backToListModalLink");
            if (backToListModalLink) {
                const referrer = document.referrer;
                if (referrer && new URL(referrer).pathname.endsWith("orders_list.php")) {
                    const params = new URL(referrer).searchParams;
                    const page = params.get("page") || "1";
                    const search = params.get("search") || "";
                    backToListModalLink.href = `orders_list.php?page=${page}&search=${encodeURIComponent(search)}`;
                } else {
                    backToListModalLink.href = "orders_list.php";
                }
            }
        }

        form.addEventListener("submit", function(e) {
            e.preventDefault();
            if (!checkForm()) {
                if (itemsTbody.querySelectorAll('tr').length === 0) {
                    restoreDefaultModalState('資料錯誤', '', '訂單至少需有一項商品。');
                } else {
                    restoreDefaultModalState('資料錯誤', '', '請檢查所有必填欄位，紅框標示處皆須正確填寫。');
                }
                successAlert.style.display = 'none';
                warningAlert.style.display = 'block';
                modal.show();
                return;
            }

            const fd = new FormData(form);
            fetch("orders_edit-api.php", {
                    method: "POST",
                    body: fd
                })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            try {
                                const errData = JSON.parse(text);
                                throw new Error(errData.message || errData.error || "伺服器回應錯誤：" + res.status);
                            } catch (e) {
                                throw new Error("伺服器回應錯誤：" + res.status + " " + text.substring(0, 100));
                            }
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        restoreDefaultModalState('編輯結果', data.message || "訂單已成功更新！", '');
                        successAlert.style.display = 'block';
                        warningAlert.style.display = 'none';
                    } else {
                        restoreDefaultModalState('編輯失敗', '', data.message || data.error || "更新失敗，請檢查資料或稍後再試。");
                        successAlert.style.display = 'none';
                        warningAlert.style.display = 'block';
                    }
                    modal.show();
                })
                .catch(err => {
                    console.error('Fetch Error:', err);
                    restoreDefaultModalState('請求錯誤', '', err.message || "請求過程中發生錯誤，請檢查網路連線。");
                    successAlert.style.display = 'none';
                    warningAlert.style.display = 'block';
                    modal.show();
                });
        });

        const backToListButton = document.querySelector('a.btn.btn-secondary[href="orders_list.php"]');
        if (backToListButton) {
            const referrer = document.referrer;
            if (referrer && new URL(referrer).pathname.endsWith("orders_list.php")) {
                const params = new URL(referrer).searchParams;
                const page = params.get("page") || "1";
                const search = params.get("search") || "";
                backToListButton.href = `orders_list.php?page=${page}&search=${encodeURIComponent(search)}`;
            }
        }
        const initialBackToListModalLink = document.getElementById("backToList");
        if (initialBackToListModalLink && initialBackToListModalLink.id === "backToList") {
            initialBackToListModalLink.id = "backToListModalLink";
            const referrer = document.referrer;
            if (referrer && new URL(referrer).pathname.endsWith("orders_list.php")) {
                const params = new URL(referrer).searchParams;
                const page = params.get("page") || "1";
                const search = params.get("search") || "";
                initialBackToListModalLink.href = `orders_list.php?page=${page}&search=${encodeURIComponent(search)}`;
            } else {
                initialBackToListModalLink.href = "orders_list.php";
            }
        }
    });
</script>
<?php include __DIR__ . '/parts/html-tail.php' ?>