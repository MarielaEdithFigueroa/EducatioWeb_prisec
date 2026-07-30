export type User = {
    id: number;
    login: string;
    nombre: string;
    apellido: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};
