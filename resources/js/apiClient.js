function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : "";
}

export async function ensureCsrf() {
    await fetch("/sanctum/csrf-cookie", { credentials: "include" });
}

export async function apiFetch(url, options = {}) {
    const method = (options.method || "GET").toUpperCase();
    if (method !== "GET" && method !== "HEAD") {
        await ensureCsrf();
    }

    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        ...(options.headers || {}),
    };

    if (method !== "GET" && method !== "HEAD") {
        headers["X-XSRF-TOKEN"] = getCsrfToken();
    }

    const response = await fetch(url, {
        ...options,
        credentials: "include",
        headers,
    });

    let data = null;
    const contentType = response.headers.get("content-type") || "";
    if (contentType.includes("application/json")) {
        data = await response.json();
    }

    return { response, data };
}
