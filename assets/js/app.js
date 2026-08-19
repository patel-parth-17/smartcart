/* =========================================================
   SMARTCART AI
========================================================= */


function openAI() {

    const modal =
        document.getElementById("aiModal");

    if (!modal) {
        return;
    }

    modal.classList.add("active");

    document.body.style.overflow = "hidden";


    setTimeout(function () {

        const input =
            document.getElementById("aiQuestion");

        if (input) {
            input.focus();
        }

    }, 100);

}


function closeAI() {

    const modal =
        document.getElementById("aiModal");

    if (!modal) {
        return;
    }

    modal.classList.remove("active");

    document.body.style.overflow = "";

}


function askSuggestion(question) {

    const input =
        document.getElementById("aiQuestion");

    if (!input) {
        return;
    }

    input.value = question;

    input.focus();

}


/* =========================================================
   AI FORM
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const form =
            document.getElementById("aiForm");

        if (!form) {
            return;
        }


        form.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();


                const input =
                    document.getElementById(
                        "aiQuestion"
                    );

                const messages =
                    document.getElementById(
                        "aiMessages"
                    );

                const button =
                    document.getElementById(
                        "aiButton"
                    );


                const question =
                    input.value.trim();


                if (!question) {
                    return;
                }


                /* USER MESSAGE */

                addUserMessage(
                    messages,
                    question
                );


                input.value = "";

                button.disabled = true;

                button.textContent =
                    "Thinking...";


                /* LOADING */

                const loading =
                    addBotMessage(
                        messages,
                        "🤔 I'm checking the SmartCart products..."
                    );


                try {

                    const formData =
                        new FormData();

                    formData.append(
                        "question",
                        question
                    );


                    const response =
                        await fetch(
                            "/smartcart/api/gemini.php",
                            {
                                method: "POST",
                                body: formData
                            }
                        );


                    if (!response.ok) {

                        throw new Error(
                            "Server error"
                        );

                    }


                    const data =
                        await response.json();


                    /* REMOVE LOADING */

                    if (loading) {
                        loading.remove();
                    }


                    if (!data.success) {

                        addBotMessage(
                            messages,
                            "❌ " +
                            (
                                data.message ||
                                "Something went wrong."
                            )
                        );

                        return;
                    }


                    /* RESPONSE */

                    addBotMessage(
                        messages,
                        data.message
                    );


                    /* PRODUCTS */

                    if (
                        Array.isArray(
                            data.products
                        ) &&
                        data.products.length > 0
                    ) {

                        addProductResults(
                            messages,
                            data.products
                        );

                    }


                } catch (error) {

                    if (loading) {
                        loading.remove();
                    }


                    addBotMessage(
                        messages,
                        "❌ Unable to connect to SmartCart. Please try again."
                    );

                    console.error(
                        error
                    );

                }


                button.disabled = false;

                button.textContent =
                    "Send";


                messages.scrollTop =
                    messages.scrollHeight;

            }
        );

    }
);


/* =========================================================
   USER MESSAGE
========================================================= */

function addUserMessage(
    container,
    text
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "ai-chat-message";

    wrapper.style.justifyContent =
        "flex-end";


    const bubble =
        document.createElement("div");

    bubble.className =
        "chat-bubble";

    bubble.style.background =
        "#7c3aed";

    bubble.style.color =
        "#ffffff";

    bubble.style.whiteSpace =
        "pre-line";

    bubble.textContent =
        text;


    wrapper.appendChild(
        bubble
    );


    container.appendChild(
        wrapper
    );


    container.scrollTop =
        container.scrollHeight;

}


/* =========================================================
   BOT MESSAGE
========================================================= */

function addBotMessage(
    container,
    text
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "ai-chat-message";


    const avatar =
        document.createElement("div");

    avatar.className =
        "chat-avatar";

    avatar.textContent =
        "🤖";


    const bubble =
        document.createElement("div");

    bubble.className =
        "chat-bubble";

    bubble.style.whiteSpace =
        "pre-line";

    bubble.textContent =
        text;


    wrapper.appendChild(
        avatar
    );

    wrapper.appendChild(
        bubble
    );


    container.appendChild(
        wrapper
    );


    container.scrollTop =
        container.scrollHeight;


    return wrapper;

}


/* =========================================================
   PRODUCT RESULTS
========================================================= */

function addProductResults(
    container,
    products
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "ai-result-products";


    products.forEach(
        function (product) {

            const card =
                document.createElement("div");

            card.className =
                "ai-result-card";


            const image =
                document.createElement("img");

            image.src =
                product.image ||
                "/smartcart/assets/images/no-image.svg";

            image.alt =
                product.name || "Product";

            image.onerror =
                function () {

                    this.onerror = null;

                    this.src =
                        "/smartcart/assets/images/no-image.svg";

                };


            const body =
                document.createElement("div");

            body.className =
                "ai-result-body";


            const name =
                document.createElement("h3");

            name.textContent =
                product.name;


            const price =
                document.createElement("div");

            price.className =
                "ai-result-price";

            price.textContent =
                "₹" +
                Number(
                    product.price
                ).toFixed(2);


            const rating =
                document.createElement("div");

            rating.textContent =
                "⭐ " +
                Number(
                    product.rating
                ).toFixed(1);


            const link =
                document.createElement("a");

            link.className =
                "btn";

            link.href =
                "/smartcart/product.php?id=" +
                Number(product.id);

            link.textContent =
                "View Product";


            body.appendChild(
                name
            );

            body.appendChild(
                price
            );

            body.appendChild(
                rating
            );

            body.appendChild(
                document.createElement("br")
            );

            body.appendChild(
                link
            );


            card.appendChild(
                image
            );

            card.appendChild(
                body
            );


            wrapper.appendChild(
                card
            );

        }
    );


    container.appendChild(
        wrapper
    );


    container.scrollTop =
        container.scrollHeight;

}


/* =========================================================
   ESC KEY CLOSE
========================================================= */

document.addEventListener(
    "keydown",
    function (event) {

        if (
            event.key === "Escape"
        ) {

            closeAI();

        }

    }
);