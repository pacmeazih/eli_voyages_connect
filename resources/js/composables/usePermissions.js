import { usePage } from '@inertiajs/vue3';

/**
 * Check if user has a specific permission
 */
export function can(permission) {
    const page = usePage();
    const permissions = page.props.auth?.user?.permissions || [];
    return permissions.includes(permission);
}

/**
 * Check if user has any of the specified permissions
 */
export function canAny(permissionsArray) {
    const page = usePage();
    const userPermissions = page.props.auth?.user?.permissions || [];
    return permissionsArray.some(permission => userPermissions.includes(permission));
}

/**
 * Check if user has all of the specified permissions
 */
export function canAll(permissionsArray) {
    const page = usePage();
    const userPermissions = page.props.auth?.user?.permissions || [];
    return permissionsArray.every(permission => userPermissions.includes(permission));
}

/**
 * Check if user has a specific role
 */
export function hasRole(role) {
    const page = usePage();
    const roles = page.props.auth?.user?.roles || [];
    return roles.includes(role);
}

/**
 * Check if user has any of the specified roles
 */
export function hasAnyRole(rolesArray) {
    const page = usePage();
    const userRoles = page.props.auth?.user?.roles || [];
    return rolesArray.some(role => userRoles.includes(role));
}
