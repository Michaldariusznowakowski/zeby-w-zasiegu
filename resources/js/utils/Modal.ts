

const isOpenClass: string = "modal-is-open";
const openingClass: string = "modal-is-opening";
const closingClass: string = "modal-is-closing";
const animationDuration: number = 400; // ms
let visibleModal: HTMLElement | null = null;
const toggleModal = (event: MouseEvent): void => {
    event.preventDefault();
    if (event.currentTarget === null) {
        return;
    }
    if (event.currentTarget instanceof HTMLElement === false) {
        return;
    }
    if (event.currentTarget.hasAttribute("data-target") === false) {
        return;
    }
    const modalId = event.currentTarget.getAttribute("data-target");
    if (modalId === null) {
        return;
    }
    const modal = document.getElementById(modalId);

    if (modal !== null && isModalOpen(modal)) {
        closeModal(modal);
    } else if (modal !== null) {
        openModal(modal);
    }
};
const isModalOpen = (modal: HTMLElement): boolean => {
    return modal.hasAttribute("open") && modal.getAttribute("open") !== "false";
};
const openModal = (modal: HTMLElement): void => {
    if (isScrollbarVisible()) {
        document.documentElement.style.setProperty("--scrollbar-width", `${getScrollbarWidth()}px`);
    }
    document.documentElement.classList.add(isOpenClass, openingClass);
    setTimeout(() => {
        visibleModal = modal;
        document.documentElement.classList.remove(openingClass);
    }, animationDuration);
    modal.setAttribute("open", "true");
};
const closeModal = (modal: HTMLElement): void => {
    visibleModal = null;
    document.documentElement.classList.add(closingClass);
    setTimeout(() => {
        document.documentElement.classList.remove(closingClass, isOpenClass);
        document.documentElement.style.removeProperty("--scrollbar-width");
        modal.removeAttribute("open");
    }, animationDuration);
};
document.addEventListener("click", (event: MouseEvent) => {
    if (visibleModal !== null) {
        const modalContent = visibleModal.querySelector("article");
        const isClickInside = modalContent?.contains(event.target as Node);
        if (!isClickInside) {
            closeModal(visibleModal);
        }
    }
});
document.addEventListener("keydown", (event: KeyboardEvent) => {
    if (event.key === "Escape" && visibleModal !== null) {
        closeModal(visibleModal);
    }
});
const getScrollbarWidth = (): number => {
    const outer = document.createElement("div");
    outer.style.visibility = "hidden";
    outer.style.overflow = "scroll";
    document.body.appendChild(outer);
    const inner = document.createElement("div");
    outer.appendChild(inner);
    const scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
    outer.parentNode?.removeChild(outer);
    return scrollbarWidth;
};
const isScrollbarVisible = (): boolean => {
    return document.body.scrollHeight > screen.height;
};
function acceptSetCheckboxValue(event: MouseEvent): void {
    if (event.currentTarget === null) {
        return;
    }
    if (event.currentTarget instanceof HTMLElement === false) {
        return;
    }
    if (event.currentTarget.hasAttribute("data-target") === false) {
        return;
    }
    const dataTarget = event.currentTarget.getAttribute("data-target");
    if (dataTarget === null) {
        return;
    }
    const targets = dataTarget.split(' ');
    const dialog = targets[0];
    const checkbox = targets[1];
    const modal = document.getElementById(checkbox);
    const dialogModal = document.getElementById(dialog);
    if (dialogModal === null) {
        return;
    }
    if (dialogModal instanceof HTMLElement) {
        closeModal(dialogModal);
    }
    console.log(modal);
    console.log(dialogModal);
    if (modal === null) {
        return;
    }
    if (modal instanceof HTMLInputElement) {
        modal.setAttribute("checked", "true");
        modal.checked = true;
    }

}
function denySetCheckboxValue(event: MouseEvent): void {
    if (event.currentTarget === null) {
        return;
    }
    if (event.currentTarget instanceof HTMLElement === false) {
        return;
    }
    if (event.currentTarget.hasAttribute("data-target") === false) {
        return;
    }
    const dataTarget = event.currentTarget.getAttribute("data-target");
    if (dataTarget === null) {
        return;
    }
    const targets = dataTarget.split(' ');
    const dialog = targets[0];
    const checkbox = targets[1];
    const modal = document.getElementById(checkbox);
    const dialogModal = document.getElementById(dialog);
    if (dialogModal === null) {
        return;
    }
    if (dialogModal instanceof HTMLElement) {
        closeModal(dialogModal);
    }
    if (modal === null) {
        return;
    }
    if (modal instanceof HTMLInputElement) {
        modal.removeAttribute("checked");
        modal.checked = false;
    }
}

// Global variables
(window as any).toggleModal = toggleModal;
(window as any).acceptSetCheckboxValue = acceptSetCheckboxValue;
(window as any).denySetCheckboxValue = denySetCheckboxValue;

// Export
export { toggleModal, acceptSetCheckboxValue, denySetCheckboxValue, openModal, closeModal };