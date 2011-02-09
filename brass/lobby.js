var cbmessages = [
    "The time limits for this game indicate that the game's creator probably wants to play the game in near-real time. You should only join if you have a good stretch of time to spare and are prepared to play the game in a sitting. Failure to play rapidly when it is your turn may result in your being kicked from the game.",
    "The time limits for this game indicate that the game's creator probably wants to play through the game in the space of a day or less. You should only join if you will be able to check back frequently to make your moves until the game is over. Failure to play rapidly when it is your turn may result in your being kicked from the game.",
    "The time limits for this game indicate that the game's creator probably wants players to check the game several times a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game.",
    "The time limits for this game indicate that the game's creator probably wants players to check the game at least a couple of times a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game.",
    "The time limits for this game indicate that the game's creator probably wants players to check the game at least once a day to make their moves. You should only join if you can make this commitment. Delaying for too long when it is your turn may result in your being kicked from the game."
];

function ChangeActionID (newActionID) {
    document.getElementById('FormActionIDid').value = newActionID;
    return true;
}

function ConfirmTimeLimit (newActionID, cbmessageID) {
    document.getElementById('FormActionIDid').value = newActionID;
    return confirm( cbmessages[cbmessageID] +
                    " Click 'OK' to continue, or 'Cancel' if you do not want to join after all."
                    );
}