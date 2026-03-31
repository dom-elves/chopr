export const notificationTypes = {
    DebtCreatedNotification: (notificationData) => ({
        classes : 'fa-list text-green-300',
        message: `You have been added to a debt of £${notificationData.amount} for ${notificationData.name} in ${notificationData.group_name} by ${notificationData.owner.name}`
    }),
    GroupUserCreatedNotification: (notificationData) => ({
        classes : 'fa-users text-blue-300',
        message: `You have been added to the group ${notificationData.name} by ${notificationData.group_owner}`
    }),
}