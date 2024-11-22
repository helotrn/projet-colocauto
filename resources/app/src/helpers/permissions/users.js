function isGlobalAdmin(user) {
  return user?.role === "admin";
}

function userIsSame(userA, userB) {
  return userA && userB && userA.id === userB.id;
}

function userIsApproved(user) {
  for (const community of user.communities) {
    if (!!community.approved_at && !community.suspended_at) {
      return true;
    }
  }
  return false;
}

const registeredRequiredFields = ["name"];
function userIsRegistered(user) {
  for (let i = 0, len = registeredRequiredFields.length; i < len; i += 1) {
    if (!user[registeredRequiredFields[i]]) {
      return false;
    }
  }

  return true;
}

function canAssignFleet(accessingUser) {
  if (!accessingUser) {
    return false;
  }

  // Must be global admin to assign fleet users.
  return isGlobalAdmin(accessingUser);
}

function canChangeUserPassword(accessingUser, accessedUser) {
  if (!accessingUser) {
    return false;
  }

  // One can change their own password.
  if (userIsSame(accessingUser, accessedUser)) {
    return true;
  }

  // Global admins can too.
  return isGlobalAdmin(accessingUser);
}

function canEditUser(accessingUser, accessedUser) {
  if (!accessingUser) {
    return false;
  }

  // One can change their own dtails.
  if (userIsSame(accessingUser, accessedUser)) {
    return true;
  }

  return isGlobalAdmin(accessingUser);
}

function canEditDriversProfile(accessingUser, accessedUser) {
  if (!accessingUser) {
    return false;
  }

  if (userIsSame(accessingUser, accessedUser)) {
    return true;
  }

  return isGlobalAdmin(accessingUser);
}

function canManageGlobalAdmins(accessingUser) {
  return accessingUser && isGlobalAdmin(accessingUser);
}

function canSeeDeletedUser(accessingUser) {
  return isGlobalAdmin(accessingUser);
}

function canSeeAdmins(accessingUser) {
  return isGlobalAdmin(accessingUser);
}

function canLoanCar(accessingUser) {
  return accessingUser?.borrower?.approved_at && !accessingUser?.borrower?.suspended_at;
}

function canSeeUserBills(accessingUser) {
  return isGlobalAdmin(accessingUser);
}

export {
  canAssignFleet,
  canChangeUserPassword,
  canEditDriversProfile,
  canEditUser,
  canLoanCar,
  canManageGlobalAdmins,
  canSeeAdmins,
  canSeeDeletedUser,
  canSeeUserBills,
  isGlobalAdmin,
  userIsApproved,
  userIsRegistered,
  userIsSame,
};
