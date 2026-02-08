// composables/usePermissions.js
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const user = computed(() => usePage().props.auth?.user || {});
  const permissions = computed(() => user.value?.permissions || []);

  const hasPermission = (permissionSlug) => {
    return permissions.value.includes(permissionSlug) || permissions.value.includes('admin_access');
  };

  const hasAnyPermission = (permissionSlugs) => {
    return permissionSlugs.some(slug => permissions.value.includes(slug)) || permissions.value.includes('admin_access');
  };

  const hasAllPermissions = (permissionSlugs) => {
    return permissionSlugs.every(slug => permissions.value.includes(slug)) || permissions.value.includes('admin_access');
  };

  // Add a specific function for admin check
  const isAdmin = () => {
    return permissions.value.includes('admin_access');
  };

  return {
    user,
    permissions,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    isAdmin
  };
}