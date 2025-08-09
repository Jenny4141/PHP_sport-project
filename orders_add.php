<?php
include __DIR__ . '/parts/init.php';

$title = '新增訂單';
$pageName = 'orders_add';

$r = [
  'member_id' => '',
  'delivery' => '',
  'fee' => 0,
  'payment' => '',
  'address' => '',
  'invoice' => '',
  'status' => '',
];

$items = [];

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

  .modal-body .alert-success {
    display: none;
  }

  .modal-body .alert-warning {
    display: none;
  }
</style>

<div class="container px-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-9">
      <div class="card mb-5">
        <div class="card-body">
          <h5 class="card-title">新增訂單</h5>
          <form id="orderForm" name="form1" novalidate>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="member_id" class="form-label">會員ID</label>
                <input type="text" class="form-control" id="member_id" name="member_id" value="<?= htmlentities($r['member_id']) ?>" data-required="true">
                <div class="invalid-feedback">會員ID為必填</div>
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
                </tbody>
              </table>
            </div>
            <button type="button" class="btn btn-success btn-sm mb-3" id="addItemBtn"><i class="fa-solid fa-plus"></i> 新增商品</button>
            <div id="orderItemsError" class="form-text text-danger mb-3" style="display: none;"></div>

            <div class="mt-3">
              <label for="itemsSubtotalDisplay" class="form-label">商品總金額</label>
              <input type="text" class="form-control" id="itemsSubtotalDisplay" value="$0" disabled>
            </div>
            <div class="mt-3">
              <label for="finalTotalDisplay" class="form-label">訂單應付金額</label>
              <input type="text" class="form-control" id="finalTotalDisplay" value="$0" disabled>
            </div>
            <div class="mt-3 col-12 col-md-10">
              <button type="submit" class="btn btn-primary" id="submitOrderBtn">新增</button>
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
        <h1 class="modal-title fs-5" id="exampleModalLabel">新增結果</h1><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-success" role="alert">成功新增訂單！</div>
        <div class="alert alert-warning" role="alert">資料未送出或格式錯誤</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="continueAddingBtnModal">繼續新增</button>
        <a id="backToListModalLink" class="btn btn-primary" href="orders_list.php">回列表頁</a>
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
    const orderItemsErrorEl = document.getElementById("orderItemsError");
    let newItemIdx = 0;

    const itemsSubtotalEl = document.getElementById("itemsSubtotalDisplay");
    const feeEl = document.getElementById("fee");
    const finalTotalDisplayEl = document.getElementById("finalTotalDisplay");

    const mainInputs = form.querySelectorAll('[data-required="true"]');
    const deliveryEl = document.getElementById("delivery");
    const memberIdEl = document.getElementById("member_id");

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
        validateInput(feeEl, true);
      }
      updateFinalTotalDisplay();
    }

    function updateItemsSubtotal() {
      let currentSubtotal = 0;
      itemsTbody.querySelectorAll('tr').forEach(row => {
        const priceInput = row.querySelector('.price-input');
        const quantityInput = row.querySelector('.item-quantity');
        if (priceInput && quantityInput && quantityInput.value) {
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
        if (prodId === currentProdId) {
          option.selected = true;
        }
        selectEl.appendChild(option);
      }
    }

    function renderSpecOpts(specSelectEl, prodId, currentSpecId) {
      specSelectEl.innerHTML = '<option value="">選擇款式</option>';
      const priceDisplay = specSelectEl.closest('tr').querySelector('.price-display');
      const priceInput = specSelectEl.closest('tr').querySelector('.price-input');
      if (priceDisplay) priceDisplay.textContent = '0';
      if (priceInput) priceInput.value = '0';

      if (prodId && specsByProduct[prodId]) {
        specsByProduct[prodId].forEach(spec => {
          const option = document.createElement('option');
          option.value = spec.spec_id;
          option.textContent = spec.color;
          option.dataset.price = spec.price;
          if (spec.spec_id.toString() === currentSpecId) {
            option.selected = true;
            if (priceDisplay) priceDisplay.textContent = parseFloat(spec.price).toLocaleString('en-US', {
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            });
            if (priceInput) priceInput.value = spec.price;
          }
          specSelectEl.appendChild(option);
        });
      }
    }

    function validateInput(inputField, showVisualError = true) {
      let isValid = true;
      const feedbackEl = inputField.nextElementSibling;
      let defaultMsgSet = false;

      if (showVisualError) {
        inputField.classList.remove('is-invalid');
        if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
          if (inputField.id === 'member_id') {
            feedbackEl.textContent = "會員ID為必填";
            defaultMsgSet = true;
          } else if (inputField.id === 'delivery') {
            feedbackEl.textContent = "物流方式為必填";
            defaultMsgSet = true;
          } else if (inputField.id === 'fee') {
            feedbackEl.textContent = "運費為必填且不可為負數";
            defaultMsgSet = true;
          } else if (inputField.id === 'payment') {
            feedbackEl.textContent = "付款方式為必填";
            defaultMsgSet = true;
          } else if (inputField.id === 'address') {
            feedbackEl.textContent = "住址為必填";
            defaultMsgSet = true;
          } else if (inputField.id === 'invoice') {
            feedbackEl.textContent = "發票類型為必填";
            defaultMsgSet = true;
          } else if (inputField.id === 'status') {
            feedbackEl.textContent = "訂單狀態為必填";
            defaultMsgSet = true;
          } else if (inputField.classList.contains('product-select')) {
            feedbackEl.textContent = "請選擇商品";
            defaultMsgSet = true;
          } else if (inputField.classList.contains('spec-select')) {
            feedbackEl.textContent = "請選擇款式";
            defaultMsgSet = true;
          } else if (inputField.classList.contains('item-quantity')) {
            feedbackEl.textContent = `請輸入有效數量 (至少${inputField.min || 1})`;
            defaultMsgSet = true;
          } else if (inputField.dataset.required) {
            feedbackEl.textContent = "此欄位為必填";
            defaultMsgSet = true;
          }
        }
      }

      if (inputField.dataset.required && inputField.value.trim() === "") {
        isValid = false;
      }
      if (inputField.type === "number") {
        if (inputField.id === 'fee' && (isNaN(parseFloat(inputField.value)) || parseFloat(inputField.value) < 0)) {
          isValid = false;
          if (feedbackEl && showVisualError) feedbackEl.textContent = "運費為必填且不可為負數";
        } else if (inputField.id !== 'fee' && inputField.classList.contains('item-quantity') && (isNaN(parseInt(inputField.value)) || parseInt(inputField.value) < parseInt(inputField.min || "1"))) {
          isValid = false;
          if (feedbackEl && showVisualError) feedbackEl.textContent = `請輸入有效數量 (至少${inputField.min || 1})`;
        }
      }
      if (inputField.type === "select-one" && inputField.dataset.required && inputField.value === "") {
        isValid = false;
      }

      if (inputField.id === 'member_id' && inputField.dataset.required) {
        const memberIdValue = inputField.value.trim();
        if (memberIdValue === "") {
          isValid = false;
        } else if (isNaN(parseInt(memberIdValue)) || parseInt(memberIdValue) <= 0) {
          isValid = false;
          if (feedbackEl && showVisualError) feedbackEl.textContent = "會員ID必須為有效數字";
        }
      }

      if (showVisualError) {
        if (!isValid) {
          inputField.classList.add('is-invalid');
        } else {
          inputField.classList.remove('is-invalid');
          if (feedbackEl && feedbackEl.classList.contains('invalid-feedback') && defaultMsgSet) {
            if (isValid) feedbackEl.textContent = "";
          }
        }
      }
      return isValid;
    }

    function checkForm(showVisualErrors = true) {
      let isFormValid = true;
      mainInputs.forEach(input => {
        if (!validateInput(input, showVisualErrors)) {
          isFormValid = false;
        }
      });

      const itemRows = itemsTbody.querySelectorAll('tr');
      itemRows.forEach(row => {
        const prodSel = row.querySelector('.product-select');
        const specSel = row.querySelector('.spec-select');
        const qtyIn = row.querySelector('.item-quantity');
        let rowIsValid = true;

        if (prodSel) {
          if (!validateInput(prodSel, showVisualErrors)) rowIsValid = false;
        }
        if (specSel) {
          if (!validateInput(specSel, showVisualErrors)) rowIsValid = false;
        }
        if (qtyIn) {
          if (!validateInput(qtyIn, showVisualErrors)) rowIsValid = false;
        }
        if (!rowIsValid) isFormValid = false;
      });
      return isFormValid;
    }

    updateFee();
    updateItemsSubtotal();

    if (deliveryEl) {
      deliveryEl.addEventListener('change', function() {
        updateFee();
      });
    }

    mainInputs.forEach(input => {
      if (input.id === 'fee' || input.id === 'delivery') return;
      const eventType = (input.tagName === 'SELECT') ? 'change' : 'input';
      input.addEventListener(eventType, () => {
        validateInput(input, true);
      });
      input.addEventListener('blur', () => {
        validateInput(input, true);
      });
    });

    itemsTbody.addEventListener('change', function(e) {
      const target = e.target;
      let recheck = false;
      if (target.classList.contains('product-select')) {
        renderSpecOpts(target.closest('tr').querySelector('.spec-select'), target.value, null);
        updateItemsSubtotal();
        recheck = true;
      } else if (target.classList.contains('spec-select')) {
        const selectedOpt = target.options[target.selectedIndex];
        const priceDisp = target.closest('tr').querySelector('.price-display');
        const priceIn = target.closest('tr').querySelector('.price-input');
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
        recheck = true;
      }

      if (recheck) {
        const row = target.closest('tr');
        if (row) {
          validateInput(row.querySelector('.product-select'), true);
          validateInput(row.querySelector('.spec-select'), true);
        }
      }
    });

    itemsTbody.addEventListener('input', function(e) {
      const target = e.target;
      if (target.classList.contains('item-quantity')) {
        validateInput(target, true);
        updateItemsSubtotal();
      }
    });

    addItemBtn.addEventListener("click", () => {
      newItemIdx++;
      const newRow = document.createElement("tr");
      newRow.classList.add("new-item-row");
      const itemKey = `new_${newItemIdx}`;
      newRow.innerHTML = `
            <td>
                <select class="form-select product-select" name="new_items[${itemKey}][product_id]" data-required="true"><option value="">選擇商品</option></select>
                <div class="invalid-feedback text-start">請選擇商品</div>
            </td>
            <td>
                <select class="form-select spec-select" name="new_items[${itemKey}][spec_id]" data-required="true"><option value="">選擇款式</option></select>
                <div class="invalid-feedback text-start">請選擇款式</div>
            </td>
            <td>$<span class="price-display">0</span><input type="hidden" class="price-input" name="new_items[${itemKey}][price_at_order]" value="0"></td>
            <td>
                <input type="number" class="form-control item-quantity" name="new_items[${itemKey}][quantity]" min="1" value="1" data-required="true">
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
      if (orderItemsErrorEl.style.display === "block") {
        orderItemsErrorEl.textContent = "";
        orderItemsErrorEl.style.display = "none";
      }
    });

    itemsTbody.addEventListener("click", function(e) {
      const deleteLink = e.target.closest("a.delete-btn");
      if (!deleteLink) return;
      e.preventDefault();
      const currentRow = deleteLink.closest("tr");
      currentRow.remove();
      updateItemsSubtotal();
      if (itemsTbody.querySelectorAll('tr').length === 0 && orderItemsErrorEl.style.display === "block") {
        orderItemsErrorEl.textContent = '訂單至少需要一個商品項目。';
      } else if (itemsTbody.querySelectorAll('tr').length > 0 && orderItemsErrorEl.style.display === "block") {
        orderItemsErrorEl.textContent = "";
        orderItemsErrorEl.style.display = "none";
      }
    });

    function restoreDefaultModalState(title, successMsg, warningMsg) {
      modalTitleEl.textContent = title || '新增結果';
      successAlert.textContent = successMsg || '成功新增訂單！';
      warningAlert.textContent = warningMsg || '資料未送出或格式錯誤';

      modalFooterEl.innerHTML = `
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="continueAddingBtnInModalFooter">繼續新增</button>
                <a id="backToListModalLinkInner" class="btn btn-primary" href="orders_list.php">回列表頁</a>`;

      const backToListModalLinkInner = document.getElementById("backToListModalLinkInner");
      if (backToListModalLinkInner) {
        const referrer = document.referrer;
        if (referrer && new URL(referrer).pathname.endsWith("orders_list.php")) {
          const params = new URL(referrer).searchParams;
          const page = params.get("page") || "1";
          const search = params.get("search") || "";
          backToListModalLinkInner.href = `orders_list.php?page=${page}&search=${encodeURIComponent(search)}`;
        } else {
          backToListModalLinkInner.href = "orders_list.php";
        }
      }
    }

    form.addEventListener("submit", function(e) {
      e.preventDefault();
      if (orderItemsErrorEl) {
        orderItemsErrorEl.textContent = "";
        orderItemsErrorEl.style.display = "none";
      }

      let isFormGenerallyValid = checkForm(true);
      let hasItems = itemsTbody.querySelectorAll('tr').length > 0;

      if (!hasItems && orderItemsErrorEl) {
        orderItemsErrorEl.textContent = '訂單至少需要一個商品項目。';
        orderItemsErrorEl.style.display = "block";
      }

      if (!isFormGenerallyValid || !hasItems) {
        const firstInvalidField = form.querySelector('.is-invalid');
        if (firstInvalidField) {
          firstInvalidField.focus();
        } else if (!hasItems && addItemBtn) {
          addItemBtn.focus();
        }
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 新增中...';

      const fd = new FormData(form);
      fetch("orders_add-api.php", {
          method: "POST",
          body: fd
        })
        .then(res => {
          if (!res.ok) {
            return res.text().then(text => {
              try {
                const errData = JSON.parse(text);
                throw new Error(errData.message || errData.error || "伺服器回應錯誤：" + res.status);
              } catch (parseError) {
                throw new Error("伺服器回應錯誤：" + res.status + " " + text.substring(0, 100));
              }
            });
          }
          return res.json();
        })
        .then(data => {
          if (data.success) {
            restoreDefaultModalState('新增成功', data.message || "訂單已成功建立！", '');
            successAlert.style.display = 'block';
            warningAlert.style.display = 'none';
            modal.show();
            form.reset();
            itemsTbody.innerHTML = '';
            if (orderItemsErrorEl) {
              orderItemsErrorEl.textContent = "";
              orderItemsErrorEl.style.display = "none";
            }
            updateFee();
            updateItemsSubtotal();
          } else {
            restoreDefaultModalState('新增失敗', '', data.message || data.error || "新增失敗，請檢查資料或稍後再試。");
            successAlert.style.display = 'none';
            warningAlert.style.display = 'block';
            modal.show();
          }
        })
        .catch(err => {
          console.error('Fetch Error:', err);
          restoreDefaultModalState('請求錯誤', '', err.message || "請求過程中發生錯誤，請檢查網路連線。");
          successAlert.style.display = 'none';
          warningAlert.style.display = 'block';
          modal.show();
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '建立訂單';
        });
    });

    const continueAddingBtnModalElem = document.getElementById("continueAddingBtnModal");
    if (continueAddingBtnModalElem) {
      continueAddingBtnModalElem.addEventListener('click', () => {
        modal.hide();
      });
    }

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

    const initialBackToListModalLink = document.getElementById("backToListModalLink");
    if (initialBackToListModalLink) {
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