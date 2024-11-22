import { isGlobalAdmin } from "@/helpers/permissions/users";

export function isOwner(user, loanable) {
  return loanable?.owner?.user.id === user.id;
}

export function isCoowner(user, loanable) {
  return loanable?.coowners?.filter((c) => c.user.id === user.id).length > 0;
}

export function isCoownerOrOwner(user, loanable) {
  return isOwner(user, loanable) || isCoowner(user, loanable);
}

export function canCreateLoanable(user) {
  return isGlobalAdmin(user);
}

export function isAdminOfLoanable(user, loanable) {
  return isGlobalAdmin(user);
}

export function canChangeOwner(user, loanable) {
  return isGlobalAdmin(user);
}

export function canEditLoanable(user, loanable) {
  return (
    isGlobalAdmin(user) || isAdminOfLoanable(user, loanable) || isCoownerOrOwner(user, loanable)
  );
}

export function canDeleteLoanable(user, loanable) {
  return isOwner(user, loanable) || isGlobalAdmin(user);
}

export function canAddCoowner(user, loanable) {
  return isOwner(user, loanable) || isGlobalAdmin(user);
}

export function canRemoveCoowner(user, loanable, coowner) {
  return isOwner(user, loanable) || coowner.user.id === user.id || isGlobalAdmin(user);
}

export function canEditCoowner(user, loanable, coowner) {
  return isOwner(user, loanable) || coowner.user.id === user.id || isGlobalAdmin(user);
}

export function canEditCoownerPaidAmounts(user, loanable) {
  return isOwner(user, loanable) || isGlobalAdmin(user);
}
