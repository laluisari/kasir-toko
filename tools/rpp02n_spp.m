#import <IOBluetooth/IOBluetooth.h>
#import <Foundation/Foundation.h>

static void printDeviceAddr(IOBluetoothDevice *d) {
    NSString *a = [d addressString];
    printf("addr=%s", a ? a.UTF8String : "?");
}

int main(int argc, const char * argv[]) {
    @autoreleasepool {
        NSString *target = argc > 1 ? [NSString stringWithUTF8String:argv[1]] : @"RPP02N";
        NSArray *devices = [IOBluetoothDevice pairedDevices];
        IOBluetoothDevice *chosen = nil;
        for (IOBluetoothDevice *d in devices) {
            NSString *name = d.name ?: @"?";
            printf("Paired: %s ", name.UTF8String);
            printDeviceAddr(d);
            printf("\n");
            if ([name.lowercaseString containsString:target.lowercaseString]) chosen = d;
        }
        if (!chosen) { printf("[FAIL] device %s not in paired list\n", target.UTF8String); return 2; }
        printf("Using: %s ", chosen.name.UTF8String);
        printDeviceAddr(chosen);
        printf("\n");

        for (IOBluetoothSDPServiceRecord *svc in [chosen services]) {
            BluetoothRFCOMMChannelID ch = 0;
            IOReturn rc = [svc getRFCOMMChannelID:&ch];
            NSString *sname = [svc getServiceName] ?: @"?";
            printf("SDP: %s channel=%u rc=%d\n", sname.UTF8String, (unsigned)ch, rc);
        }

        for (int ch = 1; ch <= 8; ch++) {
            IOBluetoothRFCOMMChannel *channel = nil;
            IOReturn rc = [chosen openRFCOMMChannelSync:&channel withChannelID:ch delegate:nil];
            if (rc != kIOReturnSuccess || channel == nil) { printf("ch %d open FAIL rc=%d\n", ch, rc); continue; }
            printf("ch %d OPEN OK\n", ch);

            const char *msg = "=== SPP RAW TEST ===\nHELLO RPP02N\n\n\n";
            uint8_t buf[128];
            size_t n = strlen(msg);
            memcpy(buf, msg, n);
            printf("  write text -> %d\n", [channel write:buf length:(UInt16)n]);
            usleep(300000);

            uint8_t initc[2] = {0x1B, 0x40};
            printf("  write init -> %d\n", [channel write:initc length:2]);
            usleep(150000);

            uint8_t cutc[4] = {0x1D, 0x56, 0x42, 0x00};
            printf("  write cut  -> %d\n", [channel write:cutc length:4]);

            usleep(600000);
            [channel closeChannel];
            [chosen closeConnection];
            printf("ch %d done\n", ch);
            return 0;
        }
        printf("[FAIL] no RFCOMM channel usable\n");
        return 3;
    }
}
