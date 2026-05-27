import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { apiFetch } from "./apiClient";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const refreshUser = useCallback(async () => {
        try {
            const { data } = await apiFetch("/api/user");
            setUser(data?.user ?? null);
        } catch {
            setUser(null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        refreshUser();
    }, [refreshUser]);

    const login = useCallback(async (email, password) => {
        const { response, data } = await apiFetch("/api/login", {
            method: "POST",
            body: JSON.stringify({ email, password }),
        });
        if (!response.ok) {
            throw new Error(data?.message || data?.errors?.email?.[0] || "Login failed.");
        }
        setUser(data.user);
        return data.user;
    }, []);

    const register = useCallback(async (payload) => {
        const { response, data } = await apiFetch("/api/register", {
            method: "POST",
            body: JSON.stringify(payload),
        });
        if (!response.ok) {
            const msg =
                data?.message ||
                Object.values(data?.errors || {})
                    .flat()
                    .join(" ") ||
                "Registration failed.";
            throw new Error(msg);
        }
        setUser(data.user);
        return data;
    }, []);

    const resendVerificationEmail = useCallback(async () => {
        const { response, data } = await apiFetch("/api/email/resend-verification", {
            method: "POST",
        });
        if (!response.ok) {
            throw new Error(data?.message || "Could not resend verification email.");
        }
        return data.message;
    }, []);

    const logout = useCallback(async () => {
        await apiFetch("/api/logout", { method: "POST" });
        setUser(null);
    }, []);

    const value = useMemo(
        () => ({ user, loading, login, register, logout, refreshUser, resendVerificationEmail }),
        [user, loading, login, register, logout, refreshUser, resendVerificationEmail]
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    const ctx = useContext(AuthContext);
    if (!ctx) {
        throw new Error("useAuth must be used within AuthProvider");
    }
    return ctx;
}
