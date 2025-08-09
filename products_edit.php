<?php
include __DIR__ . '/parts/init.php';

$title = '編輯商品';
$pageName = 'products_edit';

$spec_id = isset($_GET['spec_id']) ? intval($_GET['spec_id']) : 0;
if ($spec_id === 0) {
    header("Location: products_list.php");
    exit;
}

$stmt_spec = $pdo->prepare("SELECT * FROM specs WHERE spec_id = ?");
$stmt_spec->execute([$spec_id]);
$spec = $stmt_spec->fetch();

if (!$spec) {
    header("Location: products_list.php");
    exit;
}

$product_id_from_spec = $spec['product_id'];
$stmt_product = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt_product->execute([$product_id_from_spec]);
$product = $stmt_product->fetch();

if (!$product) {
    header("Location: products_list.php");
    exit;
}

$brands = $pdo->query("SELECT brand_id, name FROM brands")->fetchAll();
$sports = $pdo->query("SELECT id, name FROM sports")->fetchAll();

$stmt_images = $pdo->prepare("SELECT * FROM images WHERE product_id = ? ORDER BY image_order ASC");
$stmt_images->execute([$product['product_id']]);
$images = $stmt_images->fetchAll();


$all_specs_for_this_product = [];
$stmt_all_specs = $pdo->prepare("SELECT * FROM specs WHERE product_id = ? ORDER BY spec_id");
$stmt_all_specs->execute([$product['product_id']]);
$all_specs_for_this_product = $stmt_all_specs->fetchAll();

?>
<?php include __DIR__ . '/parts/html-head.php' ?>
<?php include __DIR__ . '/parts/html-aside.php' ?>
<?php include __DIR__ . '/parts/html-navbar.php' ?>

<div class="container">

    <?php if ($product):
    ?>
        <div class="row mt-3 mb-4">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-10">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">此商品其他規格</h5>
                        <?php if (count($all_specs_for_this_product) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>款式</th>
                                            <th>單價</th>
                                            <th>數量</th>
                                            <th>材質</th>
                                            <th>尺寸</th>
                                            <th>重量</th>
                                            <th>產地</th>
                                            <th>狀態</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_specs_for_this_product as $index => $s_item): ?>
                                            <tr class="<?= ($s_item['spec_id'] == $spec_id) ? 'table-light' : '' ?>">
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($s_item['color']) ?></td>
                                                <td>$<?= number_format($s_item['price']) ?></td>
                                                <td><?= htmlspecialchars($s_item['stock']) ?></td>
                                                <td><?= htmlspecialchars($s_item['material']) ?></td>
                                                <td><?= htmlspecialchars($s_item['size']) ?></td>
                                                <td><?= htmlspecialchars($s_item['weight']) ?> g</td>
                                                <td><?= htmlspecialchars($s_item['origin']) ?></td>
                                                <td>
                                                    <?php if ($s_item['spec_id'] != $spec_id): ?>
                                                        <a href="products_edit.php?spec_id=<?= $s_item['spec_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fa-solid fa-pen-to-square me-1"></i>編輯
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger text-light"><i class="fa-solid fa-pencil me-1"></i>目前編輯中</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">此商品目前沒有其他規格可顯示。</p>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="products_add_spec.php?product_id=<?= $product['product_id'] ?>" class="btn btn-success btn-sm text-light ">
                                <i class="fa-solid fa-plus-circle me-1"></i>新增規格
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
    <?php endif; ?>
    <form id="productForm" enctype="multipart/form-data" onsubmit="submitForm(event)" novalidate>
        <input type="hidden" name="spec_id" value="<?= $spec['spec_id'] ?>">
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
        <div class="row mb-1">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-7 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="product_name" class="form-label">商品名稱</label>
                            <input type="text" class="form-control" id="product_name" name="product_name" value="<?= htmlspecialchars($product['name']) ?>" required>
                            <div class="form-text text-danger product_name_error"></div>
                        </div>
                        <div class="mb-3 d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="w-100 w-md-50">
                                <label for="sport" class="form-label">運動種類</label>
                                <select class="form-select" id="sport" name="sport" required>
                                    <option value="">請選擇運動種類</option>
                                    <?php foreach ($sports as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $product['sport_id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-danger sport_error"></div>
                            </div>
                            <div class="w-100 w-md-50">
                                <label for="brand" class="form-label">品牌</label>
                                <select class="form-select" id="brand" name="brand" required>
                                    <option value="">請選擇品牌</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= $b['brand_id'] ?>" <?= $b['brand_id'] == $product['brand_id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-danger brand_error"></div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="card-subtitle mb-2 text-muted">此規格詳細資料 (ID: <?= $spec['spec_id'] ?>)</h6>
                        <div class="mb-3 d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="w-100 w-md-50">
                                <label for="stock" class="form-label">數量</label>
                                <input type="number" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($spec['stock']) ?>" required min="0">
                                <div class="form-text text-danger stock_error"></div>
                            </div>
                            <div class="w-100 w-md-50">
                                <label for="price" class="form-label">單價</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($spec['price']) ?>" required min="0">
                                <div class="form-text text-danger price_error"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="material" class="form-label">材質</label>
                            <input type="text" class="form-control" id="material" name="material" value="<?= htmlspecialchars($spec['material']) ?>" required>
                            <div class="form-text text-danger material_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="size" class="form-label">尺寸 (公分)</label>
                            <input type="text" class="form-control" id="size" name="size" value="<?= htmlspecialchars($spec['size']) ?>" required>
                            <div class="form-text text-danger size_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="weight" class="form-label">重量 (公克)</label>
                            <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="<?= htmlspecialchars($spec['weight']) ?>" required min="0">
                            <div class="form-text text-danger weight_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="color" class="form-label">款式</label>
                            <input type="text" class="form-control" id="color" name="color" value="<?= htmlspecialchars($spec['color']) ?>" required>
                            <div class="form-text text-danger color_error"></div>
                        </div>
                        <div class="mb-3">
                            <label for="origin" class="form-label">產地</label>
                            <input type="text" class="form-control" id="origin" name="origin" value="<?= htmlspecialchars($spec['origin']) ?>" required>
                            <div class="form-text text-danger origin_error"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">商品圖片</h5>
                        <input type="file" id="productImages" name="productImages[]" multiple accept="image/*" style="display: none;" onchange="handleFiles(this.files)">
                        <label for="productImages" class="btn btn-success btn-sm text-light mb-2">
                            <i class="fa-solid fa-image me-1"></i> 選擇圖片
                        </label>
                        <div id="previewContainer" class="d-flex flex-wrap gap-2 mt-2 p-2" style="min-height: 80px;">
                            <?php foreach ($images as $img): ?>
                                <?php if (!empty($img['image_url'])): ?>
                                    <div class="position-relative existing-image-wrapper" data-id="<?= $img['image_id'] ?>" style="width: 80px; height: 80px;">
                                        <img src="<?= $imageBasePath . htmlspecialchars($img['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="商品圖片">
                                        <button type="button" class="btn-close position-absolute top-0 end-0 bg-light p-1" aria-label="移除此圖片" onclick="markImageForDeletion(this)"></button>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div id="imageError" class="form-text text-danger"></div>
                    </div>
                </div>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
        <div class="row mb-5">
            <div class="col-1 d-none d-md-block"></div>
            <div class="col-12 col-md-10">
                <button type="submit" class="btn btn-primary">修改</button>
                <a href="products_list.php" class="btn btn-outline-secondary ms-2">取消</a>
            </div>
            <div class="col-1 d-none d-md-block"></div>
        </div>
    </form>
</div>
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">編輯成功</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                商品已成功編輯！
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="successModalContinueButton" data-bs-dismiss="modal">繼續編輯</button>
                <a href="products_list.php" class="btn btn-primary" id="successModalToListButton">回列表頁</a>
            </div>
        </div>
    </div>
</div>
<script>
    const productForm = document.getElementById("productForm");
    const imageInput = document.getElementById("productImages");
    const previewContainer = document.getElementById("previewContainer");
    const imageError = document.getElementById("imageError");

    const updateReturnToListLinks = () => {
        const referrer = document.referrer;
        if (!referrer) return;

        try {
            const referrerUrl = new URL(referrer);
            if (referrerUrl.pathname.includes("products_list.php")) {
                const params = referrerUrl.searchParams;
                const page = params.get("page") || "1";
                const search = params.get("search") || "";

                let newHref = `products_list.php?page=${page}&search=${encodeURIComponent(search)}`;

                const formCancelButton = document.querySelector('div.d-flex.justify-content-center a.btn-dark[href="products_list.php"]');
                if (formCancelButton) {
                    formCancelButton.href = newHref;
                }

                const successModalToListButton = document.getElementById('successModalToListButton');
                if (successModalToListButton) {
                    successModalToListButton.href = newHref;
                }
            }
        } catch (e) {
            console.warn("處理 referrer URL 時發生錯誤 (product edit return links):", e);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateReturnToListLinks);
    } else {
        updateReturnToListLinks();
    }

    let newSelectedFiles = [];
    let deletedImageIds = [];

    function handleFiles(files) {
        if (imageError) imageError.textContent = '';
        for (const file of files) {
            if (!file.type.startsWith('image/')) {
                if (imageError) imageError.textContent += `${file.name} 不是圖片格式。\n`;
                continue;
            }
            if (newSelectedFiles.find(f => f.name === file.name && f.lastModified === file.lastModified)) {
                continue;
            }
            newSelectedFiles.push(file);

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement("div");
                wrapper.classList.add("position-relative", "p-1", "new-image-wrapper");
                wrapper.style.width = "88px";
                wrapper.style.height = "88px";
                wrapper.dataset.fileName = file.name;

                wrapper.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border: 1px solid #ddd; border-radius: .25rem;" alt="新圖片預覽">
                    <button type="button" class="btn-close btn-sm position-absolute top-0 end-0 bg-light p-1" aria-label="移除此新圖片" onclick="removeNewPreview(this)" style="transform: translate(25%, -25%);"></button>
                `;
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        }
        imageInput.value = '';
    }

    function removeNewPreview(buttonEl) {
        const wrapper = buttonEl.closest(".new-image-wrapper");
        if (wrapper) {
            const fileNameToRemove = wrapper.dataset.fileName;
            wrapper.remove();
            newSelectedFiles = newSelectedFiles.filter(file => file.name !== fileNameToRemove);
        }
    }

    function markImageForDeletion(buttonEl) {
        const wrapper = buttonEl.closest(".existing-image-wrapper");
        if (!wrapper) return;
        const imageId = wrapper.dataset.id;

        if (wrapper.classList.toggle('marked-for-deletion')) {
            wrapper.style.opacity = "0.5";
            buttonEl.classList.remove('btn-close');
            buttonEl.classList.add('btn-danger');
            buttonEl.innerHTML = '<i class="fa-solid fa-rotate-left"></i>';
            if (!deletedImageIds.includes(imageId)) {
                deletedImageIds.push(imageId);
            }
        } else {
            wrapper.style.opacity = "1";
            buttonEl.classList.add('btn-close');
            buttonEl.classList.remove('btn-danger');
            buttonEl.innerHTML = '';
            const index = deletedImageIds.indexOf(imageId);
            if (index > -1) {
                deletedImageIds.splice(index, 1);
            }
        }
    }

    const fieldsToValidateOnInput_Edit = productForm.querySelectorAll("input[required], select[required]");

    fieldsToValidateOnInput_Edit.forEach(input => {
        const eventType = input.tagName.toLowerCase() === 'select' ? 'change' : 'input';
        input.addEventListener(eventType, function() {
            const fieldName = this.name;
            const errorContainer = productForm.querySelector(`.${fieldName}_error`);

            if (this.value.trim() !== '' || (this.tagName.toLowerCase() === 'select' && this.value !== '')) {
                this.classList.remove('is-invalid');
                if (errorContainer) {
                    errorContainer.textContent = '';
                }
            } else {
                if (this.hasAttribute('required')) {
                    this.classList.add('is-invalid');
                    if (errorContainer) {
                        const label = this.previousElementSibling;
                        const fieldDisplayName = label && label.tagName === 'LABEL' ? label.firstChild.textContent.trim() : this.name;
                        errorContainer.textContent = `${fieldDisplayName} 為必填`;
                    }
                }
            }
        });

        input.addEventListener('blur', function() {
            const fieldName = this.name;
            const errorContainer = productForm.querySelector(`.${fieldName}_error`);

            if (this.value.trim() === '' && this.hasAttribute('required')) {
                this.classList.add('is-invalid');
                if (errorContainer) {
                    const label = this.previousElementSibling;
                    const fieldDisplayName = label && label.tagName === 'LABEL' ? label.firstChild.textContent.trim() : this.name;
                    errorContainer.textContent = `${fieldDisplayName} 為必填`;
                }
            } else {
                let fieldIsValidOnBlur = true;
                if ((this.id === 'price' || this.id === 'weight') && (isNaN(parseFloat(this.value)) || parseFloat(this.value) < 0)) {
                    this.classList.add('is-invalid');
                    if (errorContainer) errorContainer.textContent = (this.id === 'price' ? '單價' : '重量') + '必須是有效的數字且不可為負';
                    fieldIsValidOnBlur = false;
                }
                if (this.id === 'stock' && (isNaN(parseInt(this.value)) || parseInt(this.value) < 0)) {
                    this.classList.add('is-invalid');
                    if (errorContainer) errorContainer.textContent = '數量必須是有效的數字且不可為負';
                    fieldIsValidOnBlur = false;
                }

                if (fieldIsValidOnBlur && !this.classList.contains('is-invalid') && errorContainer) {}
            }
        });
    });

    async function submitForm(event) {
        event.preventDefault();
        let isValid = true;

        productForm.querySelectorAll('.form-text.text-danger').forEach(el => el.textContent = '');
        productForm.querySelectorAll('.form-control, .form-select').forEach(el => el.classList.remove('is-invalid'));

        productForm.querySelectorAll("input[required], select[required]").forEach(input => {
            const fieldName = input.name;
            const errorContainer = productForm.querySelector(`.${fieldName}_error`);
            if (input.value.trim() === "") {
                isValid = false;
                input.classList.add("is-invalid");
                if (errorContainer) {
                    const label = input.previousElementSibling;
                    const fieldDisplayName = label && label.tagName === 'LABEL' ? label.firstChild.textContent.trim() : input.name;
                    errorContainer.textContent = `${fieldDisplayName} 為必填`;
                }
            }
            if (input.value.trim() !== "") {
                if ((input.id === 'price' || input.id === 'weight') && (isNaN(parseFloat(input.value)) || parseFloat(input.value) < 0)) {
                    isValid = false;
                    input.classList.add("is-invalid");
                    if (errorContainer) errorContainer.textContent = (input.id === 'price' ? '單價' : '重量') + '必須是有效的數字且不可為負';
                }
                if (input.id === 'stock' && (isNaN(parseInt(input.value)) || parseInt(input.value) < 0)) {
                    isValid = false;
                    input.classList.add("is-invalid");
                    if (errorContainer) errorContainer.textContent = '數量必須是有效的數字且不可為負';
                }
            }
        });

        if (!isValid) {
            const firstInvalidField = productForm.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            return;
        }


        const submitButton = productForm.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 更新中...';
        }

        const formData = new FormData(productForm);
        newSelectedFiles.forEach(file => {
            formData.append("productImages[]", file);
        });
        deletedImageIds.forEach(id => {
            formData.append("deleted_ids[]", id);
        });


        if (newSelectedFiles.length > 0 && formData.has('productImages[]')) {
            const fileEntries = formData.getAll('productImages[]');

        }


        try {
            const response = await fetch("products_edit-api.php", {
                method: "POST",
                body: formData
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();

            if (result.success) {
                const modalEl = document.getElementById('successModal');
                const modalInstance = new bootstrap.Modal(modalEl);

                modalEl.addEventListener('hidden.bs.modal', function onModalHidden() {
                    location.reload();
                    modalEl.removeEventListener('hidden.bs.modal', onModalHidden);
                });
                modalInstance.show();


                deletedImageIds = [];
                newSelectedFiles = [];


            } else {
                let userMessage = '更新失敗。';
                if (result.error) {
                    userMessage += '原因：' + result.error;
                } else {
                    userMessage += '請稍後再試。';
                    console.error('API錯誤:', result);
                }
                alert(userMessage);
            }
        } catch (error) {
            console.error("送出表單時發生錯誤:", error);
            alert("更新過程中發生網路或伺服器錯誤，請稍後再試。");
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = '更新此規格';
            }
        }
    }
</script>
<?php include __DIR__ . '/parts/html-scripts.php' ?>
<?php include __DIR__ . '/parts/html-tail.php' ?>