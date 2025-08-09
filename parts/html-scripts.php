</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    // ✅ 內部跳轉維持子選單高亮，首頁會都收起來
    if (window.location.pathname.includes("index_.php")) {
      localStorage.removeItem("activeMenu");
      localStorage.removeItem("activeParent");
      localStorage.removeItem("activeSubMenu");
    }

    const path = window.location.pathname.split("/").pop(); // 取得當前頁面的檔案名稱

    // 定義各類系統的母選單
    const baseMapping = {
      "venues": "reservationSubmenu",
      "courts": "reservationSubmenu",
      "courts_timeslots": "reservationSubmenu",
      "timeslots": "reservationSubmenu",
      "reservations": "reservationSubmenu",
      "products": "shopSubmenu",
      "orders": "shopSubmenu",
      "teams": "groupSubmenu",
      "classes": "courseSubmenu",
      "coaches": "courseSubmenu",
      "booking": "courseSubmenu",
      "members": "memberSubmenu"
    };

    // 自動生成完整的 menuMapping
    const menuMapping = {};
    for (const key in baseMapping) {
      menuMapping[`${key}_list.php`] = baseMapping[key];
      menuMapping[`${key}_add.php`] = baseMapping[key];
      menuMapping[`${key}_edit.php`] = baseMapping[key];
    }

    // 設定選單展開與高亮
    if (menuMapping[path]) {
      localStorage.setItem("activeMenu", menuMapping[path]); // 展開母選單
      localStorage.setItem("activeParent", menuMapping[path]); // 保持母選單展開

      // 更精確地判斷 `add.php` 和 `edit.php`，確保正確指向 `list.php`
      if (path.includes("_add.php") || path.includes("_edit.php")) {
        const baseName = path.replace("_add.php", "_list.php").replace("_edit.php", "_list.php");
        localStorage.setItem("activeSubMenu", baseName); // 指向 `list.php` 而不是 `courts_list.php`
      } else {
        localStorage.setItem("activeSubMenu", path);
      }
    }

    const sidebarItems = document.querySelectorAll("a[data-bs-toggle='collapse']");
    const subMenuLinks = document.querySelectorAll("a[href]"); // ✅ 監聽內部跳轉

    // 在頁面載入時，展開之前的選單
    const activeMenu = localStorage.getItem("activeMenu");
    const activeParent = localStorage.getItem("activeParent");
    const activeSubMenu = localStorage.getItem("activeSubMenu");

    if (activeMenu) {
      const menu = document.getElementById(activeMenu);
      if (menu) menu.classList.add("show"); // ✅ 保持展開
    }

    if (activeParent) {
      const parentMenu = document.getElementById(activeParent);
      if (parentMenu) parentMenu.classList.add("show"); // ✅ 保持母選單展開
    }

    // 恢復主選單的變色狀態
    if (activeParent) {
      const parentLink = document.querySelector(`.sidebar-link[href='#${activeParent}']`);
      if (parentLink) {
        parentLink.classList.add("active"); // ✅ 恢復主選單變色
      }
    }

    // 恢復子選單的變色狀態
    if (activeSubMenu) {
      const subMenuItem = document.querySelector(`.sidebar-link[href="${activeSubMenu}"]`);
      if (subMenuItem) {
        subMenuItem.classList.add("active"); // ✅ 恢復子選單變色狀態
      }
    }

    // 監聽母選單內的 `a`，確保跳轉時記住展開的選單
    sidebarItems.forEach(item => {
      item.addEventListener("click", () => {
        const submenuId = (item.getAttribute("href"))?.replace("#", ""); // ✅ 統一去掉 "#"
        if (submenuId) {
          localStorage.setItem("activeMenu", submenuId);
          localStorage.setItem("activeParent", submenuId);
        }
      });
    });

    subMenuLinks.forEach(link => {
      link.addEventListener("click", () => {
        const activeMenu = link.getAttribute("href")?.replace("#", ""); // ✅ 統一去掉 "#"
        const activeParent = link.closest(".collapse")?.id; // ✅ 保持母選單 ID 不變
        if (activeMenu) localStorage.setItem("activeMenu", activeMenu);
        if (activeParent) localStorage.setItem("activeParent", activeParent);
        localStorage.setItem("activeSubMenu", link.getAttribute("href")); // ✅ 儲存子選單
      });
    });

    // 導航欄點擊變色，收起恢復顏色
    document.querySelectorAll("a[data-bs-toggle='collapse']").forEach(link => {
      link.addEventListener("click", () => {
        document.querySelectorAll("a[data-bs-toggle='collapse']").forEach(l => l.classList.remove("active"));
        link.classList.add("active"); // 點擊後立即變色
      });
      const targetCollapse = document.querySelector(link.getAttribute("href"));
      if (targetCollapse) {
        targetCollapse.addEventListener("hidden.bs.collapse", () => {
          link.classList.remove("active");
        });
      }
    });

    document.getElementById("sidebarMobile").addEventListener("shown.bs.offcanvas", function() {
      const activeMenu = localStorage.getItem("activeMenu");
      if (activeMenu) {
        const menu = document.getElementById(activeMenu);
        if (menu) {
          menu.classList.add("show"); // ✅ 手動展開
        }
      }
    });
  });
</script>